<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Services\ClientRoutingService;
use Illuminate\Http\JsonResponse;

final class ClientRoutingController extends Controller
{
    public function fetch(ClientRoutingService $routing): JsonResponse
    {
        return response()->json(['data' => $routing->fetch()])
            ->header('Cache-Control', 'private, no-store');
    }
}
