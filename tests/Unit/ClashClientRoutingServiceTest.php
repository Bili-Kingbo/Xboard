<?php

namespace Tests\Unit;

use App\Services\ClashClientRoutingService;
use App\Services\ClientRoutingService;
use Tests\TestCase;

class ClashClientRoutingServiceTest extends TestCase
{
    public function test_preferred_nodes_are_manual_options_after_the_general_group(): void
    {
        $config = [
            'proxies' => [
                ['name' => 'A'],
                ['name' => 'B'],
                ['name' => 'C'],
            ],
            'proxy-groups' => [
                ['name' => 'Company', 'type' => 'select', 'proxies' => ['A', 'B', 'C']],
            ],
            'rules' => ['DOMAIN,ads.example,REJECT', 'MATCH,Company', 'DOMAIN,unreachable.example,DIRECT'],
        ];
        $nodes = [
            ['name' => 'A', 'profiles' => ['codex', 'claude']],
            ['name' => 'B', 'profiles' => ['international']],
            ['name' => 'C', 'profiles' => []],
        ];

        $result = app(ClashClientRoutingService::class)->apply(
            $config,
            app(ClientRoutingService::class)->fetch(),
            $nodes
        );

        $groups = collect($result['proxy-groups'])->keyBy('name');
        $this->assertSame(['Company', 'A'], $groups['公司 · OpenAI']['proxies']);
        $this->assertSame(['Company', 'A'], $groups['公司 · Claude']['proxies']);
        $this->assertSame(['Company', 'B'], $groups['公司 · 国外网站']['proxies']);
        $this->assertSame(['Company'], $groups['公司 · Shedio']['proxies']);
        $this->assertContains('DOMAIN-SUFFIX,openai.com,公司 · OpenAI', $result['rules']);
        $this->assertContains('DOMAIN-SUFFIX,anthropic.com,公司 · Claude', $result['rules']);
        $this->assertContains('GEOSITE,CN,DIRECT', $result['rules']);
        $this->assertContains('GEOIP,CN,DIRECT', $result['rules']);
        $this->assertSame('MATCH,公司 · 国外网站', end($result['rules']));
        $this->assertContains('DOMAIN,ads.example,REJECT', $result['rules']);
        $this->assertNotContains('DOMAIN,unreachable.example,DIRECT', $result['rules']);
    }

    public function test_unavailable_assigned_nodes_never_appear_in_a_policy_group(): void
    {
        $config = [
            'proxies' => [['name' => 'Visible']],
            'proxy-groups' => [['name' => 'Company', 'type' => 'select', 'proxies' => ['Visible']]],
            'rules' => [],
        ];

        $result = app(ClashClientRoutingService::class)->apply(
            $config,
            app(ClientRoutingService::class)->fetch(),
            [['name' => 'Hidden', 'profiles' => ['codex']]]
        );

        $group = collect($result['proxy-groups'])->firstWhere('name', '公司 · OpenAI');
        $this->assertSame(['Company'], $group['proxies']);
    }

    public function test_direct_profile_routes_to_direct_without_creating_a_proxy_group(): void
    {
        $document = app(ClientRoutingService::class)->fetch();
        $document['profiles']['codex']['target'] = 'direct';
        $config = [
            'proxies' => [['name' => 'Visible']],
            'proxy-groups' => [['name' => 'Company', 'type' => 'select', 'proxies' => ['Visible']]],
            'rules' => [],
        ];

        $result = app(ClashClientRoutingService::class)->apply($config, $document, [
            ['name' => 'Visible', 'profiles' => ['codex']],
        ]);

        $this->assertContains('DOMAIN-SUFFIX,openai.com,DIRECT', $result['rules']);
        $this->assertNotContains('公司 · OpenAI', array_column($result['proxy-groups'], 'name'));
    }
}
