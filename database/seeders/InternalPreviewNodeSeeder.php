<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class InternalPreviewNodeSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local') || !config('app.internal_free_mode')) {
            throw new RuntimeException('Preview nodes may only be seeded in local internal-free mode.');
        }

        $group = ServerGroup::query()->firstOrCreate(
            ['name' => 'Engineering'],
            ['transfer_enable' => 500 * 1024 * 1024 * 1024],
        );

        User::query()->updateOrCreate(
            ['email' => 'preview@example.com'],
            [
                'password' => password_hash('preview-password-123', PASSWORD_DEFAULT),
                'uuid' => '11111111-1111-4111-8111-111111111111',
                'token' => hash('md5', 'preview@example.com'),
                'group_id' => $group->id,
                'transfer_enable' => 500 * 1024 * 1024 * 1024,
                'expired_at' => null,
                'banned' => false,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'password' => password_hash('admin-password-123', PASSWORD_DEFAULT),
                'uuid' => '22222222-2222-4222-8222-222222222222',
                'token' => hash('md5', 'admin@example.com'),
                'group_id' => $group->id,
                'transfer_enable' => 500 * 1024 * 1024 * 1024,
                'expired_at' => null,
                'banned' => false,
                'is_admin' => true,
            ],
        );

        $nodes = [
            [
                'code' => 'preview-sg-edge',
                'type' => Server::TYPE_SHADOWSOCKS,
                'name' => '新加坡 · Edge 01',
                'host' => 'sg-edge.preview.example',
                'port' => '443',
                'server_port' => 443,
                'rate' => 1,
                'sort' => 10,
                'tags' => ['新加坡', '低延迟'],
                'protocol_settings' => [
                    'cipher' => 'aes-256-gcm',
                    'plugin' => null,
                    'plugin_opts' => null,
                ],
            ],
            [
                'code' => 'preview-jp-premium',
                'type' => Server::TYPE_TROJAN,
                'name' => '东京 · Premium 02',
                'host' => 'jp-premium.preview.example',
                'port' => '443',
                'server_port' => 443,
                'rate' => 1,
                'sort' => 20,
                'tags' => ['日本', '专线'],
                'protocol_settings' => [
                    'tls' => 1,
                    'network' => 'tcp',
                    'network_settings' => null,
                    'tls_settings' => [
                        'server_name' => 'jp-premium.preview.example',
                        'allow_insecure' => false,
                    ],
                    'multiplex' => ['enabled' => false],
                ],
            ],
            [
                'code' => 'preview-us-hy2',
                'type' => Server::TYPE_HYSTERIA,
                'name' => '洛杉矶 · Hysteria 03',
                'host' => 'us-hy2.preview.example',
                'port' => '8443',
                'server_port' => 8443,
                'rate' => 1.2,
                'sort' => 30,
                'tags' => ['美国', '高带宽'],
                'protocol_settings' => [
                    'version' => 2,
                    'bandwidth' => ['up' => 100, 'down' => 500],
                    'obfs' => ['open' => false, 'type' => 'salamander', 'password' => null],
                    'tls' => [
                        'server_name' => 'us-hy2.preview.example',
                        'allow_insecure' => false,
                    ],
                    'hop_interval' => null,
                ],
            ],
            [
                'code' => 'preview-hk-backup',
                'type' => Server::TYPE_SHADOWSOCKS,
                'name' => '香港 · Backup 04',
                'host' => 'hk-backup.preview.example',
                'port' => '1443',
                'server_port' => 1443,
                'rate' => .8,
                'sort' => 40,
                'tags' => ['香港', '备用'],
                'protocol_settings' => [
                    'cipher' => 'chacha20-ietf-poly1305',
                    'plugin' => null,
                    'plugin_opts' => null,
                ],
            ],
        ];

        foreach ($nodes as $node) {
            Server::query()->updateOrCreate(
                ['type' => $node['type'], 'code' => $node['code']],
                [
                    ...$node,
                    'group_ids' => [(string) $group->id],
                    'route_ids' => [],
                    'show' => true,
                    'enabled' => true,
                    'transfer_enable' => 0,
                    'u' => 0,
                    'd' => 0,
                ],
            );
        }

        Notice::query()->updateOrCreate(
            ['title' => '公司安全网络使用须知'],
            [
                'content' => '<p>欢迎使用公司内部 VPN。请仅在受信任的公司设备上导入个人订阅链接，禁止转发或共享。</p><p>节点和访问权限会按照部门身份组自动同步。如遇连接问题，请先查看节点状态，再通过工单联系 IT 团队。</p>',
                'show' => true,
                'sort' => 1,
                'tags' => ['重要', '安全'],
            ],
        );

        Notice::query()->updateOrCreate(
            ['title' => 'Meta 订阅现已统一启用'],
            [
                'content' => '<p>内部订阅默认使用 Clash Meta 格式。复制 Dashboard 中的订阅链接并粘贴到客户端，即可自动获取当前部门组的可用节点。</p>',
                'show' => true,
                'sort' => 2,
                'tags' => ['网络更新'],
            ],
        );
    }
}
