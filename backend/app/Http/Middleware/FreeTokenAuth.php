<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FreeTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) env('FREE_API_TOKEN', '');
        if ($expected === '' || ! hash_equals($expected, (string) $request->bearerToken())) {
            return response()->json(['message' => '未授权'], 401);
        }

        return $next($request);
    }
}
