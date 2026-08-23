<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Yaml\Yaml;

class SpecialNodeImportService
{
    private const MAX_SOURCE_BYTES = 2_000_000;
    private const MAX_NODES = 500;
    private const SUPPORTED_TYPES = [
        'ss', 'vmess', 'vless', 'trojan', 'hysteria2', 'tuic', 'socks5', 'http', 'anytls',
    ];

    /**
     * Parse a remote subscription URL, Clash Meta YAML/JSON, base64 subscription,
     * or newline-separated share links into static Clash Meta proxy definitions.
     *
     * @return array{proxies: array<int, array<string, mixed>>, source_type: string, source_label: ?string}
     */
    public function parse(string $source): array
    {
        $source = trim($source);
        if ($source === '') {
            throw new ApiException('请输入订阅地址或节点内容');
        }
        if (strlen($source) > self::MAX_SOURCE_BYTES) {
            throw new ApiException('导入内容不能超过 2 MB');
        }

        $sourceType = 'content';
        $sourceLabel = null;
        if ($this->isHttpUrl($source)) {
            $sourceType = 'url';
            $sourceLabel = parse_url($source, PHP_URL_HOST) ?: null;
            $source = $this->fetchRemote($source);
        }

        $proxies = $this->parseStructured($source);
        if ($proxies === []) {
            $decoded = $this->decodeBase64($source);
            if ($decoded !== null && $decoded !== $source) {
                $proxies = $this->parseStructured($decoded);
                $source = $decoded;
            }
        }
        if ($proxies === []) {
            $proxies = $this->parseShareLinks($source);
        }
        if ($proxies === []) {
            throw new ApiException('没有解析到可用节点；请粘贴 Clash Meta YAML、订阅地址或常见分享链接');
        }
        if (count($proxies) > self::MAX_NODES) {
            throw new ApiException('单次最多导入 ' . self::MAX_NODES . ' 个节点');
        }

        return [
            'proxies' => $this->normalizeNames($proxies),
            'source_type' => $sourceType,
            'source_label' => $sourceLabel,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function parseStructured(string $source): array
    {
        $value = null;
        try {
            $value = json_decode($source, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            try {
                $value = Yaml::parse($source);
            } catch (\Throwable) {
                return [];
            }
        }

        if (!is_array($value)) {
            return [];
        }
        $items = isset($value['proxies']) && is_array($value['proxies'])
            ? $value['proxies']
            : (array_is_list($value) ? $value : []);

        return $this->normalizeProxies($items);
    }

    /** @return array<int, array<string, mixed>> */
    private function parseShareLinks(string $source): array
    {
        $items = [];
        foreach (preg_split('/\R+/', trim($source)) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $proxy = match (true) {
                str_starts_with($line, 'vmess://') => $this->parseVmess($line),
                str_starts_with($line, 'vless://') => $this->parseUrlProxy($line, 'vless'),
                str_starts_with($line, 'trojan://') => $this->parseUrlProxy($line, 'trojan'),
                str_starts_with($line, 'hysteria2://'), str_starts_with($line, 'hy2://') => $this->parseUrlProxy($line, 'hysteria2'),
                str_starts_with($line, 'tuic://') => $this->parseUrlProxy($line, 'tuic'),
                str_starts_with($line, 'ss://') => $this->parseShadowsocks($line),
                default => null,
            };
            if ($proxy !== null) {
                $items[] = $proxy;
            }
        }

        return $this->normalizeProxies($items);
    }

    /** @return array<string, mixed>|null */
    private function parseVmess(string $uri): ?array
    {
        $decoded = $this->decodeBase64(substr($uri, 8));
        if ($decoded === null) {
            return null;
        }
        try {
            $data = json_decode($decoded, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
        if (!is_array($data)) {
            return null;
        }

        $proxy = [
            'name' => (string) ($data['ps'] ?? 'VMess 特殊节点'),
            'type' => 'vmess',
            'server' => $data['add'] ?? null,
            'port' => isset($data['port']) ? (int) $data['port'] : null,
            'uuid' => $data['id'] ?? null,
            'alterId' => isset($data['aid']) ? (int) $data['aid'] : 0,
            'cipher' => $data['scy'] ?? 'auto',
            'udp' => true,
        ];
        if (!empty($data['net']) && $data['net'] !== 'tcp') {
            $proxy['network'] = $data['net'];
        }
        if (!empty($data['tls'])) {
            $proxy['tls'] = true;
        }
        if (!empty($data['sni'])) {
            $proxy['servername'] = $data['sni'];
        }
        if (($data['net'] ?? null) === 'ws') {
            $proxy['ws-opts'] = [
                'path' => $data['path'] ?? '/',
                'headers' => array_filter(['Host' => $data['host'] ?? null]),
            ];
        }
        return $proxy;
    }

    /** @return array<string, mixed>|null */
    private function parseUrlProxy(string $uri, string $type): ?array
    {
        $parts = parse_url($uri);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['port'])) {
            return null;
        }
        parse_str($parts['query'] ?? '', $query);
        $name = rawurldecode($parts['fragment'] ?? '') ?: strtoupper($type) . ' 特殊节点';
        $user = rawurldecode($parts['user'] ?? '');
        $password = rawurldecode($parts['pass'] ?? '');

        $proxy = [
            'name' => $name,
            'type' => $type,
            'server' => $parts['host'],
            'port' => (int) $parts['port'],
            'udp' => true,
        ];

        if ($type === 'vless') {
            $proxy['uuid'] = $user;
            $network = $query['type'] ?? 'tcp';
            if ($network !== 'tcp') {
                $proxy['network'] = $network;
            }
            if (($query['security'] ?? '') === 'tls') {
                $proxy['tls'] = true;
            } elseif (($query['security'] ?? '') === 'reality') {
                $proxy['tls'] = true;
                $proxy['reality-opts'] = array_filter([
                    'public-key' => $query['pbk'] ?? null,
                    'short-id' => $query['sid'] ?? null,
                ]);
            }
            if (!empty($query['flow'])) {
                $proxy['flow'] = $query['flow'];
            }
            $this->applyCommonUrlOptions($proxy, $query, $network);
        } elseif ($type === 'trojan') {
            $proxy['password'] = $user;
            $proxy['sni'] = $query['sni'] ?? $query['peer'] ?? null;
            $this->applyCommonUrlOptions($proxy, $query, $query['type'] ?? 'tcp');
        } elseif ($type === 'hysteria2') {
            $proxy['password'] = $user;
            $proxy['sni'] = $query['sni'] ?? null;
            if (!empty($query['obfs'])) {
                $proxy['obfs'] = $query['obfs'];
                $proxy['obfs-password'] = $query['obfs-password'] ?? $query['obfsPassword'] ?? null;
            }
            if ($this->isTruthy($query['insecure'] ?? $query['allowInsecure'] ?? false)) {
                $proxy['skip-cert-verify'] = true;
            }
        } elseif ($type === 'tuic') {
            $proxy['uuid'] = $user;
            $proxy['password'] = $password;
            $proxy['sni'] = $query['sni'] ?? null;
            $proxy['congestion-controller'] = $query['congestion_control'] ?? $query['congestion-controller'] ?? 'bbr';
            if ($this->isTruthy($query['allow_insecure'] ?? $query['insecure'] ?? false)) {
                $proxy['skip-cert-verify'] = true;
            }
        }

        return array_filter($proxy, static fn ($value) => $value !== null && $value !== '');
    }

    /** @param array<string, mixed> $proxy @param array<string, mixed> $query */
    private function applyCommonUrlOptions(array &$proxy, array $query, string $network): void
    {
        $serverName = $query['sni'] ?? $query['servername'] ?? null;
        if ($serverName) {
            $proxy['servername'] = $serverName;
        }
        if (!empty($query['fp'])) {
            $proxy['client-fingerprint'] = $query['fp'];
        }
        if ($this->isTruthy($query['allowInsecure'] ?? $query['insecure'] ?? false)) {
            $proxy['skip-cert-verify'] = true;
        }
        if ($network === 'ws') {
            $proxy['ws-opts'] = [
                'path' => $query['path'] ?? '/',
                'headers' => array_filter(['Host' => $query['host'] ?? null]),
            ];
        } elseif ($network === 'grpc') {
            $proxy['grpc-opts'] = ['grpc-service-name' => $query['serviceName'] ?? $query['service_name'] ?? ''];
        }
    }

    /** @return array<string, mixed>|null */
    private function parseShadowsocks(string $uri): ?array
    {
        $withoutScheme = substr($uri, 5);
        [$body, $fragment] = array_pad(explode('#', $withoutScheme, 2), 2, '');
        [$body] = explode('?', $body, 2);
        if (!str_contains($body, '@')) {
            $decoded = $this->decodeBase64($body);
            if ($decoded === null) {
                return null;
            }
            $body = $decoded;
        }
        [$userinfo, $endpoint] = array_pad(explode('@', $body, 2), 2, '');
        $decodedUserinfo = $this->decodeBase64($userinfo);
        if ($decodedUserinfo !== null && str_contains($decodedUserinfo, ':')) {
            $userinfo = $decodedUserinfo;
        }
        [$cipher, $password] = array_pad(explode(':', $userinfo, 2), 2, '');
        $endpointParts = parse_url('tcp://' . $endpoint);
        if (!$cipher || !$password || !is_array($endpointParts) || empty($endpointParts['host']) || empty($endpointParts['port'])) {
            return null;
        }
        return [
            'name' => rawurldecode($fragment) ?: 'SS 特殊节点',
            'type' => 'ss',
            'server' => $endpointParts['host'],
            'port' => (int) $endpointParts['port'],
            'cipher' => rawurldecode($cipher),
            'password' => rawurldecode($password),
            'udp' => true,
        ];
    }

    /** @param array<int, mixed> $items @return array<int, array<string, mixed>> */
    private function normalizeProxies(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $type = strtolower((string) ($item['type'] ?? ''));
            if ($type === 'shadowsocks') {
                $type = 'ss';
            } elseif ($type === 'hy2' || $type === 'hysteria') {
                $type = 'hysteria2';
            } elseif ($type === 'socks') {
                $type = 'socks5';
            }
            $name = trim((string) ($item['name'] ?? ''));
            $server = trim((string) ($item['server'] ?? ''));
            $port = filter_var($item['port'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
            if (!in_array($type, self::SUPPORTED_TYPES, true) || $name === '' || $server === '' || $port === false) {
                continue;
            }
            $item['type'] = $type;
            $item['name'] = mb_substr($name, 0, 120);
            $item['server'] = $server;
            $item['port'] = (int) $port;
            $result[] = $this->sanitizeValue($item);
        }
        return $result;
    }

    /** @param array<int, array<string, mixed>> $proxies @return array<int, array<string, mixed>> */
    private function normalizeNames(array $proxies): array
    {
        $used = [];
        foreach ($proxies as &$proxy) {
            $base = $proxy['name'];
            $name = $base;
            $suffix = 2;
            while (isset($used[$name])) {
                $name = mb_substr($base, 0, 108) . ' · ' . $suffix++;
            }
            $used[$name] = true;
            $proxy['name'] = $name;
        }
        unset($proxy);
        return $proxies;
    }

    private function fetchRemote(string $url): string
    {
        for ($redirects = 0; $redirects <= 3; $redirects++) {
            $this->assertPublicUrl($url);
            /** @var Response $response */
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['User-Agent' => 'Xboard-Special-Node-Importer/1.0'])
                ->get($url);
            if (in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                $location = $response->header('Location');
                if (!$location || !$this->isHttpUrl($location)) {
                    throw new ApiException('订阅地址跳转目标无效');
                }
                $url = $location;
                continue;
            }
            if (!$response->successful()) {
                throw new ApiException('订阅地址请求失败：HTTP ' . $response->status());
            }
            $body = $response->body();
            if (strlen($body) > self::MAX_SOURCE_BYTES) {
                throw new ApiException('远程订阅内容不能超过 2 MB');
            }
            return $body;
        }
        throw new ApiException('订阅地址跳转次数过多');
    }

    private function assertPublicUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new ApiException('只允许导入 HTTP 或 HTTPS 订阅地址');
        }
        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : array_values(array_unique(array_filter(array_merge(
                gethostbynamel($host) ?: [],
                array_column(dns_get_record($host, DNS_AAAA) ?: [], 'ipv6'),
            ))));
        if ($addresses === []) {
            throw new ApiException('订阅地址域名无法解析');
        }
        foreach ($addresses as $address) {
            if (!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new ApiException('订阅地址不能指向内网或保留地址');
            }
        }
    }

    private function isHttpUrl(string $value): bool
    {
        return preg_match('#^https?://\S+$#i', $value) === 1;
    }

    private function decodeBase64(string $value): ?string
    {
        $value = preg_replace('/\s+/', '', trim($value)) ?? '';
        if ($value === '' || preg_match('/^[A-Za-z0-9+\/_=-]+$/', $value) !== 1) {
            return null;
        }
        $value = strtr($value, '-_', '+/');
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);
        $decoded = base64_decode($value, true);
        return $decoded === false || !mb_check_encoding($decoded, 'UTF-8') ? null : trim($decoded);
    }

    private function isTruthy(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function sanitizeValue(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 12) {
            throw new ApiException('节点配置嵌套层级过深');
        }
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $key => $child) {
                if (!is_int($key) && strlen($key) > 100) {
                    continue;
                }
                $clean[$key] = $this->sanitizeValue($child, $depth + 1);
            }
            return $clean;
        }
        if (is_string($value)) {
            return mb_substr($value, 0, 4096);
        }
        return is_scalar($value) || $value === null ? $value : null;
    }
}
