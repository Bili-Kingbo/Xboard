<?php

declare(strict_types=1);

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClientRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
