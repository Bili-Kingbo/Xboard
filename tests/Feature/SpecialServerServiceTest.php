<?php

namespace Tests\Feature;

use App\Http\Controllers\V2\Admin\Server\ManageController;
use App\Http\Controllers\V2\Admin\ClientRoutingController;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\SpecialServer;
use App\Models\SubscribeTemplate;
use App\Models\User;
use App\Protocols\ClashMeta;
use App\Services\SpecialNodeImportService;
use App\Services\SpecialServerService;
use App\Services\ServerService;
use App\Services\ClientRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class SpecialServerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_import_never_creates_a_native_server(): void
    {
        $group = ServerGroup::forceCreate(['name' => 'External delivery']);
        $request = Request::create('/server/manage/importSpecial', 'POST', [
            'source' => 'anytls://external-password@203.0.113.10:443/'
                . '?security=reality&sni=addons.mozilla.org&fp=chrome&pbk=public-key&sid=0123456789abcdef'
                . '#External%20AnyTLS',
            'group_ids' => [$group->id],
            'tags' => ['external'],
            'show' => true,
        ]);

        app(ManageController::class)->importSpecial($request, app(SpecialNodeImportService::class));

        $this->assertSame(0, Server::query()->count());
        $this->assertSame(1, SpecialServer::query()->count());
        $this->assertSame('anytls', SpecialServer::query()->value('type'));
        $this->assertSame('public-key', SpecialServer::query()->firstOrFail()->proxy_payload['reality-opts']['public-key']);
    }

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

    public function test_renamed_external_node_uses_its_current_name_in_subscriptions(): void
    {
        $user = $this->createUser();
        $node = $this->createNode('Before rename', ['3'], true);
        app(ManageController::class)->updateSpecial(Request::create('/server/manage/updateSpecial', 'POST', [
            'id' => $node->id,
            'name' => 'After rename',
        ]));

        $this->assertSame('After rename', $node->fresh()->proxy_payload['name']);
        $this->assertSame('After rename', SpecialServerService::getAvailableProxies($user)[0]['name']);

        // Older rows may already have diverged before the synchronized update.
        $node->update(['name' => 'Legacy renamed']);
        SubscribeTemplate::setContent(
            'clashmeta',
            "proxies: []\nproxy-groups:\n  - name: Company\n    type: select\n    proxies: []\nrules: []\n",
        );
        $config = Yaml::parse((new ClashMeta($user, []))->handle()->getContent());
        $this->assertSame('Legacy renamed', $config['proxies'][0]['name']);
        $this->assertContains('Legacy renamed', $config['proxy-groups'][0]['proxies']);
    }

    public function test_clash_routing_only_offers_nodes_available_to_the_current_user(): void
    {
        config(['app.internal_free_mode' => true]);
        $user = $this->createUser();
        Server::create([
            'name' => 'OpenAI native',
            'type' => Server::TYPE_SHADOWSOCKS,
            'host' => 'native.example.com',
            'port' => 443,
            'server_port' => 443,
            'rate' => 1,
            'show' => true,
            'group_ids' => ['3'],
            'client_routing_profile_ids' => ['codex'],
            'protocol_settings' => ['cipher' => 'aes-128-gcm'],
        ]);
        Server::create([
            'name' => 'Other group native',
            'type' => Server::TYPE_SHADOWSOCKS,
            'host' => 'other.example.com',
            'port' => 443,
            'server_port' => 443,
            'rate' => 1,
            'show' => true,
            'group_ids' => ['4'],
            'client_routing_profile_ids' => ['codex'],
            'protocol_settings' => ['cipher' => 'aes-128-gcm'],
        ]);
        $external = $this->createNode('Claude external', ['3'], true);
        $external->update(['client_routing_profile_ids' => ['claude']]);
        $other = $this->createNode('Other group external', ['4'], true);
        $other->update(['client_routing_profile_ids' => ['claude']]);
        SubscribeTemplate::setContent(
            'clashmeta',
            "proxies: []\nproxy-groups:\n  - name: Company\n    type: select\n    proxies: []\nrules:\n  - MATCH,Company\n",
        );
        $routing = app(ClientRoutingService::class);
        $document = $routing->fetch();
        $document['profiles']['codex']['rules_text'] = 'DOMAIN-SUFFIX,company-ai.example';
        $routing->save($document['profiles'], $document['revision']);

        $response = (new ClashMeta($user, ServerService::getAvailableServers($user)))->handle();
        $config = Yaml::parse($response->getContent());
        $groups = collect($config['proxy-groups'])->keyBy('name');

        $this->assertSame(['Company', 'OpenAI native'], $groups['公司 · OpenAI']['proxies']);
        $this->assertSame(['Company', 'Claude external'], $groups['公司 · Claude']['proxies']);
        $this->assertSame(['Company'], $groups['公司 · 国外网站']['proxies']);
        $this->assertSame(['OpenAI native', 'Claude external'], array_column($config['proxies'], 'name'));
        $this->assertContains('DOMAIN-SUFFIX,company-ai.example,公司 · OpenAI', $config['rules']);
        $this->assertNotContains('DOMAIN-SUFFIX,openai.com,公司 · OpenAI', $config['rules']);
        $this->assertSame('MATCH,公司 · 国外网站', end($config['rules']));
    }

    public function test_admin_preview_shows_actual_routing_without_node_credentials(): void
    {
        config(['app.internal_free_mode' => true]);
        $user = $this->createUser();
        $node = $this->createNode('Private external', ['3'], true);
        $node->update(['client_routing_profile_ids' => ['claude']]);
        SubscribeTemplate::setContent(
            'clashmeta',
            "proxies: []\nproxy-groups:\n  - name: Company\n    type: select\n    proxies: []\nrules:\n  - MATCH,Company\n",
        );

        $response = app(ClientRoutingController::class)->preview(
            Request::create('/routing/preview', 'GET', ['user_id' => $user->id])
        );
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(1, $data['node_count']);
        $this->assertStringContainsString('Private external', $data['yaml']);
        $this->assertStringContainsString('公司 · Claude', $data['yaml']);
        $this->assertStringNotContainsString('secret', $data['yaml']);
        $this->assertStringNotContainsString('external.example.com', $data['yaml']);
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
            'expired_at' => null,
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
