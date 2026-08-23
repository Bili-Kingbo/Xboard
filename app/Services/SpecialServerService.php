<?php

namespace App\Services;

use App\Models\SpecialServer;
use App\Models\User;

class SpecialServerService
{
    /** @return array<int, array<string, mixed>> */
    public static function getAvailableProxies(User $user): array
    {
        if (!$user->group_id) {
            return [];
        }

        return SpecialServer::query()
            ->whereJsonContains('group_ids', (string) $user->group_id)
            ->where('show', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (SpecialServer $server) => $server->proxy_payload)
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public static function getAvailableNodeSummaries(User $user): array
    {
        if (!$user->group_id) {
            return [];
        }

        return SpecialServer::query()
            ->whereJsonContains('group_ids', (string) $user->group_id)
            ->where('show', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (SpecialServer $server) => [
                'id' => 'special-' . $server->id,
                'type' => $server->type,
                'version' => null,
                'name' => $server->name,
                'rate' => 1,
                'tags' => array_values(array_unique(array_merge(['特殊节点'], $server->tags ?? []))),
                'is_online' => 1,
                'is_special' => true,
                'cache_key' => 'special-' . $server->id . '-' . optional($server->updated_at)->timestamp,
                'last_check_at' => null,
            ])
            ->all();
    }
}
