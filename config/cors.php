<?php

$defaultAllowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'http://localhost:80',
    'http://127.0.0.1:80',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:4173',
    'http://127.0.0.1:4173',
    'https://localhost',
    'https://127.0.0.1',
    'https://localhost:443',
    'https://127.0.0.1:443',
];

$allowedOrigins = array_values(array_filter(array_map(
    static fn(string $origin): string => trim($origin),
    explode(',', env('CORS_ALLOWED_ORIGINS', implode(',', $defaultAllowedOrigins)))
)));

return [
    'paths' => [
        'api/*',
        'broadcasting/auth',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],
    'allowed_origins' => $allowedOrigins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
