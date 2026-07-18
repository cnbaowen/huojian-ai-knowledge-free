<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok', 'edition' => 'knowledge-community-free', 'stage' => 'phase-5-commercial-aligned']);
    }
}
