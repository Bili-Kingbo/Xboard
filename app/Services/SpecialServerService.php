<?php

namespace App\Services;

use App\Models\SpecialServer;
use App\Models\ServerGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SpecialServerService
{
    /**
     * 节点对用户可见的条件：命中用户所在身份组，或被单独分配给该用户。
     *
     * @param  Builder<SpecialServer>  $query
     * @return Builder<SpecialServer>
     */
    public static function scopeAvailableFor(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            if ($user->group_id) {
                $query->whereJsonContains('group_ids', (string) $user->group_id);
            }
            $query->orWhereJsonContains('user_ids', (string) $user->id);
        });
    }

    /** @return array<int, array<string, mixed>> */
    public static function getAvailableProxies(User $user): array
    {
        return self::scopeAvailableFor(SpecialServer::query(), $user)
            ->where('show', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (SpecialServer $server) => array_replace($server->proxy_payload, [
                'name' => $server->name,
            ]))
            ->values()
            ->all();
    }

    /**
     * Keep client routing metadata separate from the static proxy payload.
     *
     * @return array<int, array{proxy: array<string, mixed>, profiles: array<int, string>}>
     */
    public static function getAvailableProxyEntries(User $user): array
    {
        return self::scopeAvailableFor(SpecialServer::query(), $user)
            ->where('show', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (SpecialServer $server) => [
                'proxy' => array_replace($server->proxy_payload, [
                    'name' => $server->name,
                ]),
                'profiles' => array_values($server->client_routing_profile_ids ?? []),
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public static function getAvailableNodeSummaries(User $user): array
    {
        return self::scopeAvailableFor(SpecialServer::query(), $user)
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

    /**
     * 管理端列表：附带身份组与单独分配的用户信息。
     *
     * @return \Illuminate\Support\Collection<int, SpecialServer>
     */
    public static function listForAdmin()
    {
        $nodes = SpecialServer::query()->orderBy('sort')->orderBy('id')->get();
        if ($nodes->isEmpty()) {
            return $nodes;
        }

        $groupNames = ServerGroup::query()
            ->whereIn('id', $nodes->pluck('group_ids')->flatten()->filter()->unique()->values())
            ->pluck('name', 'id');

        $users = User::query()
            ->whereIn('id', $nodes->pluck('user_ids')->flatten()->filter()->unique()->values())
            ->get(['id', 'email'])
            ->keyBy('id');

        return $nodes->each(function (SpecialServer $node) use ($groupNames, $users) {
            $node->setAttribute('groups', collect($node->group_ids ?? [])->map(fn ($id) => [
                'id' => (int) $id,
                'name' => (string) ($groupNames[$id] ?? $id),
            ])->values());
            $node->setAttribute('users', collect($node->user_ids ?? [])->map(fn ($id) => [
                'id' => (int) $id,
                'email' => $users[$id]->email ?? ('#' . $id),
            ])->values());
        });
    }

    /**
     * 把特殊节点转换成管理端节点列表中的一行。
     *
     * @return array<string, mixed>
     */
    public static function toAdminRow(SpecialServer $node): array
    {
        return [
            'id' => 'special-' . $node->id,
            'special_id' => $node->id,
            'is_special' => true,
            'code' => null,
            'name' => $node->name,
            'type' => $node->type,
            'host' => (string) data_get($node->proxy_payload, 'server', $node->source_label ?? ''),
            'port' => (int) data_get($node->proxy_payload, 'port', 0),
            'server_port' => (int) data_get($node->proxy_payload, 'port', 0),
            'rate' => 1,
            'show' => (bool) $node->show,
            'u' => 0,
            'd' => 0,
            'transfer_enable' => 0,
            'online' => 0,
            'available_status' => 2,
            'is_online' => true,
            'parent' => null,
            'tags' => array_values($node->tags ?? []),
            'client_routing_profile_ids' => array_values($node->client_routing_profile_ids ?? []),
            'group_ids' => array_values($node->group_ids ?? []),
            'user_ids' => array_values($node->user_ids ?? []),
            'groups' => collect($node->getAttribute('groups') ?? [])->all(),
            'users' => collect($node->getAttribute('users') ?? [])->all(),
            'source_type' => $node->source_type,
            'source_label' => $node->source_label,
        ];
    }
}
