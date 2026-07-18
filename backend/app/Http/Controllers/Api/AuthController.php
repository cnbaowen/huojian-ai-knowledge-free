<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'string']]);
        $expected = (string) env('FREE_API_TOKEN', '');
        if ($expected === '' || ! hash_equals($expected, (string) $request->input('token'))) {
            return response()->json(['message' => '凭据无效'], 422);
        }
        return response()->json(['token' => $expected, 'user' => $this->user()]);
    }

    public function logout(): JsonResponse { return response()->json(['message' => '已退出']); }
    public function me(): JsonResponse { return response()->json($this->user()); }

    private function user(): array
    {
        return ['id' => 1, 'name' => 'Free Admin', 'role' => 'admin'];
    }
}
