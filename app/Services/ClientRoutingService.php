<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ClientRoutingService
{
    public const KEY = 'client_routing_profiles';
    public const IDS = ['codex', 'claude', 'domestic', 'international', 'shedio'];
    private const KINDS = [
        'DOMAIN' => 'domain',
        'DOMAIN-SUFFIX' => 'domain_suffix',
        'DOMAIN-KEYWORD' => 'domain_keyword',
        'DOMAIN-REGEX' => 'domain_regex',
        'IP-CIDR' => 'ip_cidr',
        'IP-CIDR6' => 'ip_cidr',
        'RULE-SET' => 'rule_set',
    ];

    public function defaults(): array
    {
        return json_decode(
            file_get_contents(resource_path('rules/default.client-routing.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function fetch(): array
    {
        $profiles = admin_setting(self::KEY) ?: $this->defaults();
        return $this->document($profiles);
    }

    public function save(array $input, string $revision): array
    {
        $profiles = [];
        foreach (self::IDS as $id) {
            $profile = $input[$id] ?? [];
            $profiles[$id] = [
                'target' => $profile['target'],
                'rules' => $this->parse($profile['rules_text'] ?? '', "profiles.$id.rules_text"),
            ];
        }

        // One setting row stores the complete document. Competing editors cannot
        // silently overwrite an already-saved revision.
        DB::transaction(function () use ($profiles, $revision): void {
            Setting::firstOrCreate(['name' => self::KEY], ['value' => json_encode($this->defaults())]);
            $row = Setting::where('name', self::KEY)->lockForUpdate()->firstOrFail();
            $current = $this->document($row->value);
            if (!hash_equals($current['revision'], $revision)) {
                throw ValidationException::withMessages(['revision' => '规则已被其他管理员更新，请重新载入后再保存。']);
            }
            $row->value = json_encode($profiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $row->save();
        });
        admin_setting([self::KEY => $profiles]);
        return $this->document($profiles);
    }

    public function parse(string $text, string $field = 'rules_text'): array
    {
        $rules = [];
        $seen = [];
        foreach (preg_split('/\R/u', $text) ?: [] as $index => $raw) {
            $line = trim($raw);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }
            $error = static function (string $reason) use ($field, $index): never {
                throw ValidationException::withMessages([$field => '第 '.($index + 1).' 行：'.$reason]);
            };
            if (strlen($line) > 1024 || count($rules) >= 5000) {
                $error('每行最多 1024 字节，每组最多 5000 条规则。');
            }
            $parts = explode(',', $line, 2);
            $type = strtoupper(trim($parts[0]));
            if (count($parts) === 2) {
                if (!isset(self::KINDS[$type])) {
                    $error('不支持此规则类型，请使用 DOMAIN、DOMAIN-SUFFIX、DOMAIN-KEYWORD、DOMAIN-REGEX、IP-CIDR 或 RULE-SET。');
                }
                $kind = self::KINDS[$type];
                $value = trim($parts[1]);
                if ($kind !== 'domain_regex') {
                    // Accept copied Clash/Surge lines; the client's selected
                    // destination owns routing, not an embedded DIRECT/PROXY.
                    $columns = array_map('trim', explode(',', $value));
                    $value = array_shift($columns);
                    foreach ($columns as $column) {
                        if (!in_array(strtoupper($column), ['DIRECT', 'PROXY', 'NO-RESOLVE'], true)) {
                            $error('规则后的出口字段仅支持 DIRECT / PROXY，或请去掉该字段。');
                        }
                    }
                }
            } elseif (filter_var($line, FILTER_VALIDATE_IP) || str_contains($line, '/')) {
                $kind = 'ip_cidr';
                $value = $line;
            } else {
                $kind = 'domain_suffix';
                $value = $line;
                if (str_starts_with($value, '*.')) {
                    $value = substr($value, 2);
                } elseif (str_starts_with($value, '+.')) {
                    $value = substr($value, 2);
                } elseif (str_contains($value, '*') || str_contains($value, '?')) {
                    $kind = 'domain_regex';
                    $value = '^'.str_replace(['\*', '\?'], ['.*', '.'], preg_quote(strtolower($value), '~')).'$';
                }
            }
            if ($value === '') {
                $error('规则值不能为空。');
            }
            if (in_array($kind, ['domain', 'domain_suffix'], true)) {
                $value = strtolower(rtrim(ltrim($value, '.'), '.'));
                if (strlen($value) > 253 || !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/D', $value)) {
                    $error('域名格式无效；国际化域名请填写 Punycode，正则请加 DOMAIN-REGEX 前缀。');
                }
            } elseif ($kind === 'ip_cidr') {
                $network = explode('/', $value);
                $address = $network[0];
                $bits = str_contains($address, ':') ? 128 : 32;
                if (count($network) > 2 || !filter_var($address, FILTER_VALIDATE_IP)
                    || (isset($network[1]) && (!ctype_digit($network[1]) || (int) $network[1] > $bits))) {
                    $error('IP 或 CIDR 格式无效。');
                }
                $value = $address.'/'.($network[1] ?? $bits);
            } elseif ($kind === 'rule_set') {
                if (!in_array($value, ['geosite-cn', 'geoip-cn'], true)) {
                    $error('规则集仅支持 geosite-cn 和 geoip-cn。');
                }
            } elseif ($kind === 'domain_regex') {
                // sing-box uses RE2: disallow PCRE-only lookarounds/backreferences.
                if (preg_match('/\(\?(?:[=!<>]|P[=<])|\\\\[1-9]|\(\*|\+\+|\*\+|\?\+/', $value)
                    || @preg_match('~'.str_replace('~', '\~', $value).'~u', '') === false) {
                    $error('正则无效或包含 RE2 不支持的前后查找、反向引用。');
                }
            } elseif ($kind === 'domain_keyword') {
                $value = strtolower($value);
                if (preg_match('/[\s\/]/u', $value)) {
                    $error('域名关键词不能包含空格或斜杠。');
                }
            }
            $key = $kind."\0".$value;
            if (!isset($seen[$key])) {
                $rules[] = ['kind' => $kind, 'value' => $value];
                $seen[$key] = true;
            }
        }
        return $rules;
    }

    private function document(array $profiles): array
    {
        $defaults = $this->defaults();
        $canonical = [];
        foreach (self::IDS as $id) {
            $profile = $profiles[$id] ?? $defaults[$id];
            $canonical[$id] = [
                'target' => in_array($profile['target'] ?? null, ['direct', 'proxy'], true) ? $profile['target'] : $defaults[$id]['target'],
                'rules' => is_array($profile['rules'] ?? null) ? $profile['rules'] : $defaults[$id]['rules'],
            ];
        }
        $revision = hash('sha256', json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        foreach ($canonical as &$profile) {
            $profile['rules_text'] = implode("\n", array_map(static function (array $rule): string {
                return strtoupper(str_replace('_', '-', $rule['kind'])).','.$rule['value'];
            }, $profile['rules']));
        }
        unset($profile);
        return ['schema_version' => 1, 'revision' => $revision, 'profiles' => $canonical];
    }
}
