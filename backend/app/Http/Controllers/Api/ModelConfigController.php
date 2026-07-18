<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class ModelConfigController
{
    public function index(): JsonResponse
    {
        $provider = (string) config('knowledge.provider');
        return response()->json(['data' => [
            'provider' => $provider,
            'model' => config('knowledge.chat_model'),
            'base_url_configured' => config('knowledge.base_url') !== '',
            'api_key_configured' => config('knowledge.api_key') !== '',
            'secret_returned' => false,
            'offline_ready' => $provider === 'local-extractive',
        ]]);
    }
}
