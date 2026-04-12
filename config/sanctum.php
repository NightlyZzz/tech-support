<?php

use Laravel\Sanctum\Sanctum;

$defaultStatefulDomains = [
    'localhost',
    'localhost:80',
    'localhost:443',
    'localhost:5173',
    '127.0.0.1',
    '127.0.0.1:80',
    '127.0.0.1:443',
    '127.0.0.1:5173',
    '::1',
];

$currentApplicationUrl = Sanctum::currentApplicationUrlWithPort();

if (is_string($currentApplicationUrl) && $currentApplicationUrl !== '') {
    $defaultStatefulDomains[] = $currentApplicationUrl;
}

return [
    'stateful' => array_values(array_filter(array_map(
        static fn(string $domain): string => trim($domain),
        explode(',', env('SANCTUM_STATEFUL_DOMAINS', implode(',', $defaultStatefulDomains)))
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
