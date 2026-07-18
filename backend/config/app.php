<?php

return [
    'name' => env('APP_NAME', 'Huojian AI Knowledge Free'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:18000'),
    'timezone' => 'Asia/Shanghai',
    'locale' => 'zh_CN',
    'fallback_locale' => 'zh_CN',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
];
