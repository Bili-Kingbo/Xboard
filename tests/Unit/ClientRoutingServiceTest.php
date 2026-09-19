<?php

namespace Tests\Unit;

use App\Services\ClientRoutingService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ClientRoutingServiceTest extends TestCase
{
    public function test_default_profiles_cover_the_five_internal_policies(): void
    {
        $service = app(ClientRoutingService::class);
        $document = $service->fetch();

        $this->assertSame(ClientRoutingService::IDS, array_keys($document['profiles']));
        $this->assertStringContainsString('openai.com', $document['profiles']['codex']['rules_text']);
        $this->assertStringContainsString('anthropic.com', $document['profiles']['claude']['rules_text']);
        $this->assertStringContainsString('geosite-cn', $document['profiles']['domestic']['rules_text']);
        $this->assertStringContainsString('shedio', $document['profiles']['shedio']['rules_text']);
    }

    public function test_rules_accept_common_domain_ip_and_regex_forms(): void
    {
        $rules = app(ClientRoutingService::class)->parse(<<<'RULES'
            DOMAIN-SUFFIX,example.com,PROXY
            +.internal.example
            10.0.0.0/8
            IP-CIDR6,2607:6bc0::/32,no-resolve
            IP-ASN,399358,no-resolve
            DOMAIN-REGEX,^api\\.example\\.com$,PROXY
            RULE-SET,geosite-cn
            RULES);

        $this->assertSame(['domain_suffix', 'domain_suffix', 'ip_cidr', 'ip_cidr', 'ip_asn', 'domain_regex', 'rule_set'], array_column($rules, 'kind'));
        $this->assertSame('10.0.0.0/8', $rules[2]['value']);
        $this->assertSame('2607:6bc0::/32', $rules[3]['value']);
        $this->assertSame('399358', $rules[4]['value']);
    }

    public function test_rules_reject_unsupported_or_invalid_values(): void
    {
        $service = app(ClientRoutingService::class);

        $this->expectException(ValidationException::class);
        $service->parse('DOMAIN-SUFFIX,not a domain');
    }
}
