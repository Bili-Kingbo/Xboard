<?php

namespace App\Http\Controllers\V2\Admin\Server;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $serverGroups = ServerGroup::query()
            ->orderByDesc('id')
            ->withCount('users')
            ->get();

        // 只在需要时手动加载server_count
        $serverGroups->each(function ($group) {
            $group->setAttribute('server_count', $group->server_count);
        });

        return $this->success($serverGroups);
    }

    public function save(Request $request)
    {
        $params = $request->validate([
            'id' => 'nullable|integer|exists:v2_server_group,id',
            'name' => 'required|string|max:255',
        ], [
            'name.required' => '组名不能为空',
        ]);

        if ($request->input('id')) {
            $serverGroup = ServerGroup::find($request->input('id'));
        } else {
            $serverGroup = new ServerGroup();
        }

        $serverGroup->name = $params['name'];
        return $this->success($serverGroup->save());
    }

    public function drop(Request $request)
    {
        $groupId = $request->input('id');

        $serverGroup = ServerGroup::find($groupId);
        if (!$serverGroup) {
            return $this->fail([400202, '组不存在']);
        }
        if (Server::whereJsonContains('group_ids', $groupId)->exists()) {
            return $this->fail([400, '该组已被节点所使用，无法删除']);
        }

        if (!config('app.internal_free_mode') && Plan::where('group_id', $groupId)->exists()) {
            return $this->fail([400, '该组已被订阅所使用，无法删除']);
        }
        if (User::where('group_id', $groupId)->exists()) {
            return $this->fail([400, '该组已被用户所使用，无法删除']);
        }
        return $this->success($serverGroup->delete());
    }
}
