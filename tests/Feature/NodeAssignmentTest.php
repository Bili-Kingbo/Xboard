<?php

namespace Tests\Feature;

use App\Http\Controllers\V2\Admin\Server\ManageController;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\SpecialServer;
use App\Models\User;
use App\Services\ServerService;
use App\Services\SpecialNodeImportService;
use App\Services\SpecialServerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class NodeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_node_is_delivered_to_an_individually_assigned_user(): void
    {
        $group = ServerGroup::forceCreate(['name' => 'Engineering']);
        $assigned = $this->makeUser('assigned@example.com', null);
        $other = $this->makeUser('other@example.com', $group->id);

        $nodeName = $this->makeNativeNode([
            'group_ids' => [(string) $group->id],
            'user_ids' => [(string) $assigned->id],
        ]);

        $assignedNodes = ServerService::getAvailableServers($assigned);
        $otherNodes = ServerService::getAvailableServers($other);

        $this->assertSame([$nodeName], array_column($assignedNodes, 'name'));
        $this->assertSame([$nodeName], array_column($otherNodes, 'name'));
    }

    public function test_native_node_can_be_limited_to_one_person_only(): void
    {
        $group = ServerGroup::forceCreate(['name' => 'Engineering']);
        $assigned = $this->makeUser('only-me@example.com', $group->id);
        $colleague = $this->makeUser('colleague@example.com', $group->id);

        $this->makeNativeNode([
            'group_ids' => [],
            'user_ids' => [(string) $assigned->id],
        ]);

        $this->assertSame(['Personal node'], array_column(ServerService::getAvailableServers($assigned), 'name'));
        $this->assertSame([], ServerService::getAvailableServers($colleague));
    }

    public function test_special_node_supports_group_and_per_user_delivery(): void
    {
        $group = ServerGroup::forceCreate(['name' => 'Engineering']);
        $groupUser = $this->makeUser('group-user@example.com', $group->id);
        $personalUser = $this->makeUser('personal-user@example.com', null);
        $outsider = $this->makeUser('outsider@example.com', null);

        SpecialServer::create([
            'name' => 'Group external',
            'type' => 'ss',
            'group_ids' => [(string) $group->id],
            'user_ids' => [],
            'tags' => [],
            'proxy_payload' => ['name' => 'Group external', 'type' => 'ss', 'server' => 'external.example.com', 'port' => 443, 'cipher' => 'aes-128-gcm', 'password' => 'secret'],
            'show' => true,
        ]);
        SpecialServer::create([
            'name' => 'Personal external',
            'type' => 'ss',
            'group_ids' => [],
            'user_ids' => [(string) $personalUser->id],
            'tags' => [],
            'proxy_payload' => ['name' => 'Personal external', 'type' => 'ss', 'server' => 'personal.example.com', 'port' => 443, 'cipher' => 'aes-128-gcm', 'password' => 'secret'],
            'show' => true,
        ]);

        $this->assertSame(['Group external'], array_column(SpecialServerService::getAvailableProxies($groupUser), 'name'));
        $this->assertSame(['Personal external'], array_column(SpecialServerService::getAvailableProxies($personalUser), 'name'));
        $this->assertSame([], SpecialServerService::getAvailableProxies($outsider));
    }

    public function test_external_import_can_target_a_single_user(): void
    {
        $user = $this->makeUser('solo@example.com', null);
        $request = Request::create('/server/manage/importSpecial', 'POST', [
            'source' => 'ss://YWVzLTEyOC1nY206c2VjcmV0@203.0.113.9:8388#Personal%20link',
            'group_ids' => [],
            'user_ids' => [$user->id],
            'tags' => ['personal'],
            'show' => true,
        ]);

        app(ManageController::class)->importSpecial($request, app(SpecialNodeImportService::class));

        $node = SpecialServer::query()->firstOrFail();
        $this->assertSame([(string) $user->id], $node->user_ids);
        $this->assertSame([], $node->group_ids);
        $this->assertCount(1, SpecialServerService::getAvailableProxies($user));
    }

    public function test_native_rows_expose_individually_assigned_users(): void
    {
        $user = $this->makeUser('row-user@example.com', null);
        $this->makeNativeNode([
            'group_ids' => [],
            'user_ids' => [(string) $user->id],
        ]);

        $response = app(ManageController::class)->getNodes(Request::create('/server/manage/getNodes', 'GET'));
        $rows = json_decode($response->getContent(), true)['data'];

        $this->assertCount(1, $rows);
        $this->assertSame([$user->email], array_column($rows[0]['users'], 'email'));
    }

    public function test_external_nodes_are_listed_in_the_node_management_table(): void
    {
        $user = $this->makeUser('table@example.com', null);
        SpecialServer::create([
            'name' => 'Table external',
            'type' => 'ss',
            'group_ids' => [],
            'user_ids' => [(string) $user->id],
            'tags' => [],
            'proxy_payload' => ['name' => 'Table external', 'type' => 'ss', 'server' => 'table.example.com', 'port' => 443, 'cipher' => 'aes-128-gcm', 'password' => 'secret'],
            'show' => true,
        ]);

        $response = app(ManageController::class)->getNodes(Request::create('/server/manage/getNodes', 'GET'));
        $rows = json_decode($response->getContent(), true)['data'];

        $this->assertCount(1, $rows);
        $this->assertTrue($rows[0]['is_special']);
        $this->assertSame('special-' . SpecialServer::query()->value('id'), $rows[0]['id']);
        $this->assertSame('table.example.com', $rows[0]['host']);
        $this->assertSame([$user->email], array_column($rows[0]['users'], 'email'));
    }

    private function makeUser(string $email, ?int $groupId): User
    {
        return User::create([
            'email' => $email,
            'password' => 'not-used',
            'uuid' => (string) Str::uuid(),
            'token' => substr(md5($email), 0, 32),
            'group_id' => $groupId,
            'transfer_enable' => 0,
        ]);
    }

    private function makeNativeNode(array $attributes): string
    {
        $server = Server::create(array_merge([
            'name' => 'Personal node',
            'type' => Server::TYPE_VMESS,
            'host' => '127.0.0.1',
            'port' => 443,
            'server_port' => 443,
            'rate' => '1',
            'show' => true,
            'enabled' => true,
        ], $attributes));

        return $server->name;
    }
}
