<?php

declare(strict_types=1);

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Client\ClientController;
use App\Models\User;
use App\Services\ClientRoutingService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

final class ClientRoutingController extends Controller
{
    public function fetch(ClientRoutingService $routing): JsonResponse
    {
        return response()->json(['data' => $routing->fetch()]);
    }

    public function save(Request $request, ClientRoutingService $routing): JsonResponse
    {
        $rules = [
            'revision' => ['required', 'string', 'size:64'],
            'profiles' => ['required', 'array:codex,claude,domestic,international,shedio'],
        ];
        foreach (ClientRoutingService::IDS as $id) {
            // The fetch response includes the canonical parsed `rules` array
            // alongside `target` and `rules_text`. It is read-only; the
            // service reparses rules_text, but the payload must remain
            // round-trippable when an admin edits and saves the document.
            $rules["profiles.$id"] = ['required', 'array:target,rules,rules_text'];
            $rules["profiles.$id.target"] = ['required', 'in:direct,proxy'];
            $rules["profiles.$id.rules_text"] = ['present', 'nullable', 'string', 'max:200000'];
        }
        $validated = $request->validate($rules);
        return response()->json(['data' => $routing->save($validated['profiles'], $validated['revision'])]);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:v2_user,id'],
        ]);
        $user = User::findOrFail($validated['user_id']);
        if (!(new UserService())->isAvailable($user)) {
            return response()->json(['message' => '该用户当前无法获取订阅'], 422);
        }

        $previewRequest = Request::create('/s/preview', 'GET', ['flag' => 'meta']);
        $response = app(ClientController::class)->doSubscribe($previewRequest, $user);
        $config = Yaml::parse($response->getContent());
        $routing = [
            'proxy-groups' => $config['proxy-groups'] ?? [],
            'rules' => $config['rules'] ?? [],
        ];

        return response()->json(['data' => [
            'user_id' => $user->id,
            'email' => $user->email,
            'node_count' => count($config['proxies'] ?? []),
            'yaml' => Yaml::dump($routing, 4, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE),
        ]]);
    }
}
