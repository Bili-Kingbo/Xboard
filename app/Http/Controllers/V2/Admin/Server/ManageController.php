<?php

namespace App\Http\Controllers\V2\Admin\Server;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServerSave;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\SpecialServer;
use App\Models\User;
use App\Services\ClientRoutingService;
use App\Services\ServerService;
use App\Services\SpecialNodeImportService;
use App\Services\SpecialServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ManageController extends Controller
{
    /**
     * 管理端节点列表里原生节点与外部导入节点共用一张表，
     * 外部节点的 id 形如 special-12，这里把它们拆开分别处理。
     *
     * @return array{0: array<int, int>, 1: array<int, int>}
     */
    private function splitNodeIds(array $ids): array
    {
        $nativeIds = [];
        $specialIds = [];
        foreach ($ids as $id) {
            $id = (string) $id;
            if (str_starts_with($id, 'special-')) {
                $specialIds[] = (int) substr($id, strlen('special-'));
            } elseif (ctype_digit($id)) {
                $nativeIds[] = (int) $id;
            }
        }

        return [$nativeIds, $specialIds];
    }

    public function getNodes(Request $request)
    {
        $servers = ServerService::getAllServers()->map(function ($item) {
            $item['groups'] = ServerGroup::whereIn('id', $item['group_ids'] ?? [])->get(['name', 'id']);
            $item['parent'] = $item->parent;
            return $item;
        });

        $assignedUsers = User::query()
            ->whereIn('id', $servers->pluck('user_ids')->flatten()->filter()->unique()->values())
            ->get(['id', 'email'])
            ->keyBy('id');

        $servers = $servers->map(function ($item) use ($assignedUsers) {
            $item['users'] = collect($item->user_ids ?? [])->map(fn ($id) => [
                'id' => (int) $id,
                'email' => $assignedUsers[$id]->email ?? ('#' . $id),
            ])->values();
            return $item;
        });

        $external = SpecialServerService::listForAdmin()
            ->map(fn (SpecialServer $node) => SpecialServerService::toAdminRow($node));

        return $this->success($servers->concat($external)->values());
    }

    public function getSpecialNodes(Request $request)
    {
        return $this->success(SpecialServerService::listForAdmin());
    }

    public function searchUsers(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $users = User::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('email', 'like', '%' . $keyword . '%')
                        ->orWhere('remarks', 'like', '%' . $keyword . '%');
                });
            })
            ->orderByDesc('id')
            ->limit(30)
            ->get(['id', 'email', 'group_id']);

        $groupNames = ServerGroup::query()
            ->whereIn('id', $users->pluck('group_id')->filter()->unique()->values())
            ->pluck('name', 'id');

        return $this->success($users->map(fn (User $user) => [
            'id' => $user->id,
            'email' => $user->email,
            'group_id' => $user->group_id,
            'group_name' => $groupNames[$user->group_id] ?? null,
        ]));
    }

    public function importSpecial(Request $request, SpecialNodeImportService $importer)
    {
        $params = $request->validate([
            'source' => 'required|string|max:2000000',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'required|integer|exists:v2_server_group,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'required|integer|exists:v2_user,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:40',
            'client_routing_profile_ids' => 'nullable|array',
            'client_routing_profile_ids.*' => ['string', 'distinct', Rule::in(ClientRoutingService::NODE_IDS)],
            'show' => 'nullable|boolean',
        ]);

        $parsed = $importer->parse($params['source']);
        $groupIds = collect($params['group_ids'])->map(fn ($id) => (string) $id)->unique()->values()->all();
        $userIds = collect($params['user_ids'] ?? [])->map(fn ($id) => (string) $id)->unique()->values()->all();
        $tags = collect($params['tags'] ?? [])->map(fn ($tag) => trim($tag))->filter()->unique()->values()->all();
        $show = $params['show'] ?? true;

        if (empty($groupIds) && empty($userIds)) {
            return $this->fail([422, '请至少选择一个身份组或一名用户']);
        }

        $profileIds = array_values($params['client_routing_profile_ids'] ?? []);
        $created = DB::transaction(function () use ($parsed, $groupIds, $userIds, $tags, $profileIds, $show) {
            $nextSort = (int) SpecialServer::max('sort');
            return collect($parsed['proxies'])->map(function (array $proxy) use ($parsed, $groupIds, $userIds, $tags, $profileIds, $show, &$nextSort) {
                return SpecialServer::create([
                    'name' => $proxy['name'],
                    'type' => $proxy['type'],
                    'group_ids' => $groupIds,
                    'user_ids' => $userIds,
                    'tags' => $tags,
                    'client_routing_profile_ids' => $profileIds,
                    'proxy_payload' => $proxy,
                    'source_type' => $parsed['source_type'],
                    'source_label' => $parsed['source_label'],
                    'show' => $show,
                    'sort' => ++$nextSort,
                ]);
            });
        });

        return $this->success([
            'count' => $created->count(),
            'nodes' => $created->map->only(['id', 'name', 'type', 'group_ids', 'user_ids', 'tags', 'client_routing_profile_ids', 'show']),
        ]);
    }

    public function updateSpecial(Request $request)
    {
        $params = $request->validate([
            'id' => 'required|integer|exists:v2_special_server,id',
            'name' => 'nullable|string|max:120',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:v2_server_group,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:v2_user,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:40',
            'client_routing_profile_ids' => 'nullable|array',
            'client_routing_profile_ids.*' => ['string', 'distinct', Rule::in(ClientRoutingService::NODE_IDS)],
            'show' => 'nullable|boolean',
        ]);
        $node = SpecialServer::findOrFail($params['id']);
        unset($params['id']);
        if (isset($params['name'])) {
            $params['proxy_payload'] = array_replace($node->proxy_payload, [
                'name' => $params['name'],
            ]);
        }
        if (isset($params['group_ids'])) {
            $params['group_ids'] = collect($params['group_ids'])->map(fn ($id) => (string) $id)->unique()->values()->all();
        }
        if (isset($params['user_ids'])) {
            $params['user_ids'] = collect($params['user_ids'])->map(fn ($id) => (string) $id)->unique()->values()->all();
        }
        $groupIds = $params['group_ids'] ?? $node->group_ids ?? [];
        $userIds = $params['user_ids'] ?? $node->user_ids ?? [];
        if (empty($groupIds) && empty($userIds)) {
            return $this->fail([422, '请至少保留一个身份组或一名用户']);
        }
        $node->update($params);
        return $this->success(true);
    }

    public function dropSpecial(Request $request)
    {
        $params = $request->validate([
            'id' => 'nullable|integer|exists:v2_special_server,id',
            'ids' => 'nullable|array',
            'ids.*' => 'integer|exists:v2_special_server,id',
        ]);
        $ids = collect($params['ids'] ?? [])->push($params['id'] ?? null)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return $this->fail([422, '请选择要删除的特殊节点']);
        }
        SpecialServer::whereIn('id', $ids)->delete();
        return $this->success(true);
    }

    public function sort(Request $request)
    {
        ini_set('post_max_size', '1m');
        $params = $request->validate([
            '*.id' => 'nullable',
            '*.order' => 'numeric'
        ]);

        try {
            DB::beginTransaction();
            collect($params)->each(function ($item) {
                if (isset($item['id']) && isset($item['order'])) {
                    [$nativeIds, $specialIds] = $this->splitNodeIds([$item['id']]);
                    if ($nativeIds) {
                        Server::where('id', $nativeIds[0])->update(['sort' => $item['order']]);
                    }
                    if ($specialIds) {
                        SpecialServer::where('id', $specialIds[0])->update(['sort' => $item['order']]);
                    }
                }
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->fail([500, '保存失败']);

        }
        return $this->success(true);
    }

    public function save(ServerSave $request)
    {
        $params = $request->validated();
        if (array_key_exists('user_ids', $params)) {
            $params['user_ids'] = collect($params['user_ids'] ?? [])
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values()
                ->all();
        }
        if ($request->input('id')) {
            $server = Server::find($request->input('id'));
            if (!$server) {
                return $this->fail([400202, '服务器不存在']);
            }
            try {
                $server->update($params);
                return $this->success(true);
            } catch (\Exception $e) {
                Log::error($e);
                return $this->fail([500, '保存失败']);
            }
        }

        try {
            Server::create($params);
            return $this->success(true);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '创建失败']);
        }
    }

    public function update(Request $request)
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'show' => 'nullable|integer',
            'machine_id' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:v2_user,id',
            'client_routing_profile_ids' => 'nullable|array',
            'client_routing_profile_ids.*' => ['string', 'distinct', Rule::in(ClientRoutingService::NODE_IDS)],
        ]);

        $server = Server::find($request->id);
        if (!$server) {
            return $this->fail([400202, '服务器不存在']);
        }

        if (array_key_exists('user_ids', $params)) {
            $server->user_ids = collect($params['user_ids'] ?? [])
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values()
                ->all();
        }
        if (array_key_exists('client_routing_profile_ids', $params)) {
            $server->client_routing_profile_ids = array_values($params['client_routing_profile_ids']);
        }
        if (array_key_exists('show', $params)) {
            $server->show = (int) $params['show'];
        }
        if (array_key_exists('machine_id', $params)) {
            $server->machine_id = $params['machine_id'] ?: null;
        }
        if (array_key_exists('enabled', $params)) {
            $server->enabled = (bool) $params['enabled'];
        }

        if (!$server->save()) {
            return $this->fail([500, '保存失败']);
        }

        return $this->success(true);
    }

    /**
     * 删除
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function drop(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $server = Server::find($request->id);
        if (!$server) {
            return $this->fail([400202, '服务器不存在']);
        }
        if ($server->delete() === false) {
            return $this->fail([500, '删除失败']);
        }

        return $this->success(true);
    }

    /**
     * 批量删除节点
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->fail([400, '请选择要删除的节点']);
        }

        [$nativeIds, $specialIds] = $this->splitNodeIds($ids);

        try {
            if ($specialIds) {
                SpecialServer::whereIn('id', $specialIds)->delete();
            }
            $deleted = $nativeIds ? Server::whereIn('id', $nativeIds)->delete() : 0;
            if ($deleted === false) {
                return $this->fail([500, '批量删除失败']);
            }
            return $this->success(true);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '批量删除失败']);
        }
    }

    /**
     * 重置节点流量
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetTraffic(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $server = Server::find($request->id);
        if (!$server) {
            return $this->fail([400202, '服务器不存在']);
        }

        try {
            $server->u = 0;
            $server->d = 0;
            $server->save();
            
            Log::info("Server {$server->id} ({$server->name}) traffic reset by admin");
            return $this->success(true);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '重置失败']);
        }
    }

    /**
     * 批量重置节点流量
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchResetTraffic(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        [$ids] = $this->splitNodeIds($request->input('ids'));
        if (empty($ids)) {
            return $this->fail([400, '请选择要重置的节点']);
        }

        try {
            Server::whereIn('id', $ids)->update([
                'u' => 0,
                'd' => 0,
            ]);
            
            Log::info("Servers " . implode(',', $ids) . " traffic reset by admin");
            return $this->success(true);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '批量重置失败']);
        }
    }

    /**
     * 批量更新节点属性（show等）
     */
    public function batchUpdate(Request $request)
    {
        $params = $request->validate([
            'ids' => 'required|array',
            'show' => 'nullable|integer|in:0,1',
            'enabled' => 'nullable|boolean',
            'machine_id' => 'nullable|integer',
        ]);

        $ids = $params['ids'];
        if (empty($ids)) {
            return $this->fail([400, '请选择要更新的节点']);
        }

        [$nativeIds, $specialIds] = $this->splitNodeIds($ids);

        $update = [];
        if (array_key_exists('show', $params) && $params['show'] !== null) {
            $update['show'] = (int) $params['show'];
        }
        if (array_key_exists('enabled', $params) && $params['enabled'] !== null) {
            $update['enabled'] = (bool) $params['enabled'];
        }
        if (array_key_exists('machine_id', $params)) {
            $update['machine_id'] = $params['machine_id'] ?: null;
        }

        if (empty($update)) {
            return $this->fail([400, '没有可更新的字段']);
        }

        try {
            if ($specialIds && array_key_exists('show', $update)) {
                SpecialServer::whereIn('id', $specialIds)->update(['show' => (bool) $update['show']]);
            }
            $servers = $nativeIds ? Server::whereIn('id', $nativeIds)->get() : collect();
            DB::transaction(function () use ($servers, $update) {
                /** @var Server $server */
                foreach ($servers as $server) {
                    $server->update($update);
                }
            });
            return $this->success(true);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '批量更新失败']);
        }
    }

    /**
     * 复制节点
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function copy(Request $request)
    {
        $server = Server::find($request->input('id'));
        if (!$server) {
            return $this->fail([400202, '服务器不存在']);
        }

        $copiedServer = $server->replicate();
        $copiedServer->show = false;
        $copiedServer->code = null;
        $copiedServer->u = 0;
        $copiedServer->d = 0;
        $copiedServer->save();

        return $this->success(true);
    }

    /**
     * Generate ECH (Encrypted Client Hello) key pair.
     * Returns PEM-encoded ECH key (server-side) and ECH config (client-side).
     */
    public function generateEchKey(Request $request)
    {
        $publicName = $request->input('public_name', 'ech.example.com');
        if (strlen($publicName) < 1 || strlen($publicName) > 253) {
            throw new ApiException('public_name must be a valid domain (1-253 bytes)');
        }

        // Generate X25519 key pair
        $privateKey = random_bytes(32);
        $publicKey = sodium_crypto_scalarmult_base($privateKey);

        $configId = random_int(0, 255);

        // Build ECHConfigContents (draft-ietf-tls-esni-18)
        $contents = '';
        $contents .= pack('C', $configId);                // config_id
        $contents .= pack('n', 0x0020);                   // kem_id: DHKEM(X25519)
        $contents .= pack('n', 32) . $publicKey;          // public_key (length-prefixed)
        // cipher_suites: 2 suites × 4 bytes = 8 bytes
        $contents .= pack('n', 8);                        // cipher_suites byte length
        $contents .= pack('nn', 0x0001, 0x0001);          // HKDF-SHA256 + AES-128-GCM
        $contents .= pack('nn', 0x0001, 0x0003);          // HKDF-SHA256 + ChaCha20Poly1305
        $contents .= pack('C', 0);                        // max_name_length
        $contents .= pack('C', strlen($publicName)) . $publicName;
        $contents .= pack('n', 0);                        // extensions: empty

        // ECHConfig = version(2) + length(2) + contents
        $echConfig = pack('n', 0xfe0d) . pack('n', strlen($contents)) . $contents;

        // ECHConfigList = total_length(2) + configs
        $echConfigList = pack('n', strlen($echConfig)) . $echConfig;

        // ECH Keys = private_key_len(2) + key(32) + config_len(2) + config
        $echKeysPayload = pack('n', 32) . $privateKey . pack('n', strlen($echConfig)) . $echConfig;

        $keyPem = "-----BEGIN ECH KEYS-----\n"
            . chunk_split(base64_encode($echKeysPayload), 64, "\n")
            . "-----END ECH KEYS-----";

        $configPem = "-----BEGIN ECH CONFIGS-----\n"
            . chunk_split(base64_encode($echConfigList), 64, "\n")
            . "-----END ECH CONFIGS-----";

        return $this->success([
            'key' => $keyPem,
            'config' => $configPem,
        ]);
    }
}
