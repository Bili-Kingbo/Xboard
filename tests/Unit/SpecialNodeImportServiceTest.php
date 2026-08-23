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
