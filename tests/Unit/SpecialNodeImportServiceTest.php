<?php

namespace Tests\Unit;

use App\Exceptions\ApiException;
use App\Services\SpecialNodeImportService;
use PHPUnit\Framework\TestCase;

class SpecialNodeImportServiceTest extends TestCase
{
    public function test_it_imports_clash_meta_yaml_without_rebuilding_the_proxy(): void
    {
        $result = (new SpecialNodeImportService())->parse(<<<'YAML'
proxies:
  - name: 外部 HY2
    type: hysteria2
    server: edge.example.com
    port: 443
    password: external-secret
    sni: edge.example.com
    skip-cert-verify: true
YAML);

        $this->assertSame('content', $result['source_type']);
        $this->assertCount(1, $result['proxies']);
        $this->assertSame('hysteria2', $result['proxies'][0]['type']);
        $this->assertSame('external-secret', $result['proxies'][0]['password']);
        $this->assertTrue($result['proxies'][0]['skip-cert-verify']);
    }

    public function test_it_decodes_base64_share_link_subscriptions(): void
    {
        $subscription = base64_encode(
            'vless://6de46f98-ad8b-4c4b-9c21-42c0a3f18f50@node.example.com:443'
            . '?security=tls&type=ws&sni=node.example.com&path=%2Fcompany#External%20VLESS'
        );

        $result = (new SpecialNodeImportService())->parse($subscription);

        $this->assertCount(1, $result['proxies']);
        $proxy = $result['proxies'][0];
        $this->assertSame('External VLESS', $proxy['name']);
        $this->assertSame('vless', $proxy['type']);
        $this->assertSame('node.example.com', $proxy['server']);
        $this->assertSame('/company', $proxy['ws-opts']['path']);
        $this->assertTrue($proxy['tls']);
    }

    public function test_it_imports_shadowrocket_vless_with_base64_authority(): void
    {
        $authority = base64_encode(':00000000-0000-0000-0000-000000000004@198.51.100.20:1002');
        $result = (new SpecialNodeImportService())->parse(
            "vless://{$authority}?remarks=External%20VLESS&tls=1&peer=hk.art.museum"
            . '&udp=1&xtls=2&pbk=public-key&sid=20220701&fingerprint=chrome'
        );

        $this->assertCount(1, $result['proxies']);
        $proxy = $result['proxies'][0];
        $this->assertSame('External VLESS', $proxy['name']);
        $this->assertSame('00000000-0000-0000-0000-000000000004', $proxy['uuid']);
        $this->assertSame('198.51.100.20', $proxy['server']);
        $this->assertSame(1002, $proxy['port']);
        $this->assertTrue($proxy['tls']);
        $this->assertTrue($proxy['udp']);
        $this->assertSame('hk.art.museum', $proxy['servername']);
        $this->assertSame('xtls-rprx-vision', $proxy['flow']);
        $this->assertSame('public-key', $proxy['reality-opts']['public-key']);
        $this->assertSame('20220701', $proxy['reality-opts']['short-id']);
        $this->assertSame('chrome', $proxy['client-fingerprint']);
    }

    public function test_it_imports_anytls_share_links_with_encoded_password(): void
    {
        $result = (new SpecialNodeImportService())->parse(
            'anytls://example%2Bpassword%3D%3D@203.0.113.10:24062/'
            . '?sni=addons.mozilla.org&fp=chrome&insecure=1'
            . '#AnyTLS%20External'
        );

        $this->assertCount(1, $result['proxies']);
        $proxy = $result['proxies'][0];
        $this->assertSame('AnyTLS External', $proxy['name']);
        $this->assertSame('anytls', $proxy['type']);
        $this->assertSame('example+password==', $proxy['password']);
        $this->assertSame('addons.mozilla.org', $proxy['sni']);
        $this->assertSame('chrome', $proxy['client-fingerprint']);
        $this->assertTrue($proxy['skip-cert-verify']);
    }

    public function test_it_preserves_anytls_reality_fields_for_static_delivery(): void
    {
        $result = (new SpecialNodeImportService())->parse(
            'anytls://example-password@203.0.113.10:24062/'
            . '?security=reality&sni=addons.mozilla.org&fp=chrome&pbk=public-key&sid=0123456789abcdef'
            . '#AnyTLS%20Reality'
        );

        $proxy = $result['proxies'][0];
        $this->assertSame('anytls', $proxy['type']);
        $this->assertSame('example-password', $proxy['password']);
        $this->assertSame('addons.mozilla.org', $proxy['sni']);
        $this->assertSame('chrome', $proxy['client-fingerprint']);
        $this->assertSame('public-key', $proxy['reality-opts']['public-key']);
        $this->assertSame('0123456789abcdef', $proxy['reality-opts']['short-id']);
    }

    public function test_it_accepts_every_node_type_supported_by_the_panel_subscription(): void
    {
        $result = (new SpecialNodeImportService())->parse(<<<'YAML'
proxies:
  - { name: SS, type: shadowsocks, server: ss.example.com, port: 1001, cipher: aes-128-gcm, password: secret }
  - { name: VMess, type: vmess, server: vmess.example.com, port: 1002, uuid: 00000000-0000-0000-0000-000000000001 }
  - { name: VLESS, type: vless, server: vless.example.com, port: 1003, uuid: 00000000-0000-0000-0000-000000000002 }
  - { name: Trojan, type: trojan, server: trojan.example.com, port: 1004, password: secret }
  - { name: Hysteria, type: hysteria, server: hy.example.com, port: 1005, auth-str: secret }
  - { name: Hysteria2, type: hysteria2, server: hy2.example.com, port: 1006, password: secret }
  - { name: TUIC, type: tuic, server: tuic.example.com, port: 1007, uuid: 00000000-0000-0000-0000-000000000003, password: secret }
  - { name: AnyTLS, type: anytls, server: anytls.example.com, port: 1008, password: secret }
  - { name: SOCKS, type: socks, server: socks.example.com, port: 1009, username: user, password: secret }
  - { name: HTTP, type: http, server: http.example.com, port: 1010, username: user, password: secret }
  - { name: Naive, type: naive, server: naive.example.com, port: 1011, username: user, password: secret }
  - { name: Mieru, type: mieru, server: mieru.example.com, port-range: 2012-2022, transport: TCP, username: user, password: secret }
YAML);

        $this->assertSame(
            ['ss', 'vmess', 'vless', 'trojan', 'hysteria', 'hysteria2', 'tuic', 'anytls', 'socks5', 'http', 'naive', 'mieru'],
            array_column($result['proxies'], 'type')
        );
        $this->assertSame('2012-2022', $result['proxies'][11]['port-range']);
        $this->assertArrayNotHasKey('port', $result['proxies'][11]);
    }

    public function test_it_imports_panel_share_link_formats_for_hysteria_socks_http_naive_and_mieru(): void
    {
        $httpCredentials = base64_encode('employee:http-secret');
        $socksCredentials = base64_encode('employee:socks-secret');
        $source = implode("\n", [
            'hysteria://hy.example.com:443?protocol=udp&auth=hy-secret&upmbps=30&downmbps=100&sni=hy.example.com#HY1',
            "socks://{$socksCredentials}@socks.example.com:1080#SOCKS",
            "http://{$httpCredentials}@http.example.com:8080?security=tls&sni=http.example.com#HTTP",
            'naive+https://employee:naive-secret@naive.example.com:443#Naive',
            'mierus://employee:mieru-secret@mieru.example.com?port=2090-2099&protocol=TCP&multiplexing=MULTIPLEXING_HIGH#Mieru',
        ]);

        $result = (new SpecialNodeImportService())->parse($source);

        $this->assertSame(['hysteria', 'socks5', 'http', 'naive', 'mieru'], array_column($result['proxies'], 'type'));
        $this->assertSame('hy-secret', $result['proxies'][0]['auth-str']);
        $this->assertSame('socks-secret', $result['proxies'][1]['password']);
        $this->assertTrue($result['proxies'][2]['tls']);
        $this->assertSame('naive-secret', $result['proxies'][3]['password']);
        $this->assertSame('2090-2099', $result['proxies'][4]['port-range']);
        $this->assertSame('MULTIPLEXING_HIGH', $result['proxies'][4]['multiplexing']);
    }

    public function test_it_rejects_private_subscription_urls(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('不能指向内网或保留地址');

        (new SpecialNodeImportService())->parse('http://127.0.0.1/private-subscription');
    }

    public function test_it_deduplicates_imported_names(): void
    {
        $result = (new SpecialNodeImportService())->parse(<<<'YAML'
proxies:
  - { name: Same, type: ss, server: one.example.com, port: 443, cipher: aes-128-gcm, password: one }
  - { name: Same, type: ss, server: two.example.com, port: 443, cipher: aes-128-gcm, password: two }
YAML);

        $this->assertSame(['Same', 'Same · 2'], array_column($result['proxies'], 'name'));
    }
}
