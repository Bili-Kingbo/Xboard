<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class ClashClientRoutingService
{
    private const GROUP_LABELS = [
        'codex' => 'OpenAI',
        'claude' => 'Claude',
        'shedio' => 'Shedio',
        'international' => '国外网站',
    ];

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $document
     * @param array<int, array{name: string, profiles: array<int, string>}> $nodes
     * @return array<string, mixed>
     */
    public function apply(array $config, array $document, array $nodes): array
    {
        $availableNames = array_values(array_unique(array_filter(
            array_column($config['proxies'] ?? [], 'name'),
            static fn ($name) => is_string($name) && $name !== ''
        )));
        $config['proxy-groups'] = is_array($config['proxy-groups'] ?? null) ? $config['proxy-groups'] : [];
        $groupNames = array_values(array_filter(array_column($config['proxy-groups'], 'name'), 'is_string'));
        $usedNames = array_merge($groupNames, $availableNames);
        $generalGroup = $groupNames[0] ?? null;
        if ($generalGroup === null) {
            $generalGroup = '通用代理';
            $config['proxy-groups'][] = [
                'name' => $generalGroup,
                'type' => 'select',
                'proxies' => $availableNames,
            ];
            $groupNames[] = $generalGroup;
            $usedNames[] = $generalGroup;
        }

        $profiles = $document['profiles'] ?? [];
        $targets = [];
        foreach (self::GROUP_LABELS as $id => $label) {
            $profile = $profiles[$id] ?? [];
            if (($profile['target'] ?? 'proxy') === 'direct') {
                $targets[$id] = 'DIRECT';
                continue;
            }

            $groupName = $this->uniqueGroupName($label, $usedNames);
            $usedNames[] = $groupName;
            $preferred = [];
            foreach ($nodes as $node) {
                if (in_array($id, $node['profiles'], true)
                    && in_array($node['name'], $availableNames, true)) {
                    $preferred[] = $node['name'];
                }
            }
            $config['proxy-groups'][] = [
                'name' => $groupName,
                'type' => 'select',
                // The existing general group is first. Preferred nodes only
                // become active after the user selects one in the client.
                'proxies' => array_values(array_unique(array_merge([$generalGroup], $preferred))),
            ];
            $targets[$id] = $groupName;
        }

        $generatedRules = [];
        foreach (['codex', 'claude', 'shedio', 'domestic', 'international'] as $id) {
            $profile = $profiles[$id] ?? [];
            $target = $id === 'domestic'
                ? (($profile['target'] ?? 'direct') === 'direct' ? 'DIRECT' : $generalGroup)
                : ($targets[$id] ?? $generalGroup);
            foreach ($profile['rules'] ?? [] as $rule) {
                $generatedRules[] = $this->toClashRule($rule, $target);
            }
        }

        $templateRules = [];
        foreach ($config['rules'] ?? [] as $rule) {
            if (is_string($rule) && preg_match('/^MATCH\s*,/i', trim($rule))) {
                break;
            }
            $templateRules[] = $rule;
        }
        $config['rules'] = array_merge(
            $generatedRules,
            $templateRules,
            ['MATCH,'.($targets['international'] ?? $generalGroup)]
        );

        return $config;
    }

    /** @param array<int, string> $used */
    private function uniqueGroupName(string $label, array $used): string
    {
        $base = '公司 · '.$label;
        $name = $base;
        $index = 2;
        while (in_array($name, $used, true)) {
            $name = $base.' · '.$index++;
        }
        return $name;
    }

    /** @param array{kind: string, value: string} $rule */
    private function toClashRule(array $rule, string $target): string
    {
        $kind = $rule['kind'];
        $value = $rule['value'];

        if ($kind === 'rule_set') {
            return match ($value) {
                'geosite-cn' => 'GEOSITE,CN,'.$target,
                'geoip-cn' => 'GEOIP,CN,'.$target,
                default => throw new InvalidArgumentException('Unsupported client routing rule set: '.$value),
            };
        }

        if ($kind === 'ip_cidr') {
            $kind = str_contains($value, ':') ? 'IP-CIDR6' : 'IP-CIDR';
        } else {
            $kind = strtoupper(str_replace('_', '-', $kind));
        }

        return $kind.','.$value.','.$target;
    }
}
