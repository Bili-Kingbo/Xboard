<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\User;
use App\Services\NodeSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckTrafficExceeded extends Command
{
    protected $signature = 'check:traffic-exceeded';
    protected $description = '检查流量超标用户并通知节点';

    public function handle()
    {
        $count = (int) Redis::scard('traffic:pending_check');
        if ($count <= 0) {
            return;
        }

        $pendingUserIds = array_map('intval', Redis::spop('traffic:pending_check', $count));

        $exceededUsers = User::toBase()
            ->leftJoin('v2_server_group', 'v2_server_group.id', '=', 'v2_user.group_id')
            ->whereIn('v2_user.id', $pendingUserIds)
            ->whereRaw('(v2_user.u + v2_user.d) >= CASE
                WHEN v2_server_group.transfer_enable IS NULL
                    OR v2_user.transfer_enable >= v2_server_group.transfer_enable
                THEN v2_user.transfer_enable
                ELSE v2_server_group.transfer_enable
            END')
            ->whereRaw('CASE
                WHEN v2_server_group.transfer_enable IS NULL
                    OR v2_user.transfer_enable >= v2_server_group.transfer_enable
                THEN v2_user.transfer_enable
                ELSE v2_server_group.transfer_enable
            END > 0')
            ->where('v2_user.banned', 0)
            ->select(['v2_user.id', 'v2_user.group_id'])
            ->get();

        if ($exceededUsers->isEmpty()) {
            return;
        }

        $groupedUsers = $exceededUsers->groupBy('group_id');
        $notifiedCount = 0;

        foreach ($groupedUsers as $groupId => $users) {
            if (!$groupId) {
                continue;
            }

            $userIdsInGroup = $users->pluck('id')->toArray();
            $servers = Server::whereJsonContains('group_ids', (string) $groupId)->get();

            foreach ($servers as $server) {
                if (!NodeSyncService::isNodeOnline($server->id)) {
                    continue;
                }

                NodeSyncService::push($server->id, 'sync.user.delta', [
                    'action' => 'remove',
                    'users' => array_map(fn($id) => ['id' => $id], $userIdsInGroup),
                ]);
                $notifiedCount++;
            }
        }

        $this->info("Checked " . count($pendingUserIds) . " users, notified {$notifiedCount} nodes for " . $exceededUsers->count() . " exceeded users.");
    }
}
