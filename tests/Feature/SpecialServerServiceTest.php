<?php

namespace Tests\Feature;

use App\Models\SpecialServer;
use App\Models\SubscribeTemplate;
use App\Models\User;
use App\Protocols\ClashMeta;
use App\Services\SpecialServerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class SpecialServerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_only_returns_visible_special_nodes_for_the_users_group(): void
    {
        $user = User::create([
            'email' => 'special-node@example.com',
            'password' => 'not-used',
            'uuid' => '6de46f98-ad8b-4c4b-9c21-42c0a3f18f50',
            'token' => 'special-node-test-token-00000000',
            'group_id' => 3,
            'transfer_enable' => 1024,
        ]);
        $this->createNode('Available external', ['3'], true);
        $this->createNode('Other group', ['4'], true);
        $this->createNode('Hidden external', ['3'], false);

        $proxies = SpecialServerService::getAvailableProxies($user);
        $summaries = SpecialServerService::getAvailableNodeSummaries($user);

        $this->assertSame(['Available external'], array_column($proxies, 'name'));
        $this->assertCount(1, $summaries);
        $this->assertTrue($summaries[0]['is_special']);
        $this->assertContains('特殊节点', $summaries[0]['tags']);
    }

    public function test_clash_meta_directly_includes_the_stored_special_proxy(): void
    {
        $user = $this->createUser();
        $this->createNode('Direct external', ['3'], true);
        SubscribeTemplate::setContent(
            'clashmeta',
            "proxies: []\nproxy-groups:\n  - name: Company\n    type: select\n    proxies: []\nrules: []\n",
        );

        $response = (new ClashMeta($user, []))->handle();
        $config = Yaml::parse($response->getContent());

        $this->assertSame('Direct external', $config['proxies'][0]['name']);
        $this->assertSame('external.example.com', $config['proxies'][0]['server']);
        $this->assertContains('Direct external', $config['proxy-groups'][0]['proxies']);
    }

    private function createUser(): User
    {
        return User::create([
            'email' => 'clash-special@example.com',
            'password' => 'not-used',
            'uuid' => '6de46f98-ad8b-4c4b-9c21-42c0a3f18f51',
            'token' => 'clash-special-test-token-0000000',
            'group_id' => 3,
            'transfer_enable' => 1024,
        ]);
    }

    private function createNode(string $name, array $groupIds, bool $show): SpecialServer
    {
        return SpecialServer::create([
            'name' => $name,
            'type' => 'ss',
            'group_ids' => $groupIds,
            'tags' => [],
            'proxy_payload' => [
                'name' => $name,
                'type' => 'ss',
                'server' => 'external.example.com',
                'port' => 443,
                'cipher' => 'aes-128-gcm',
                'password' => 'secret',
            ],
            'show' => $show,
        ]);
    }
}
