<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    'rate_limits' => [
        'login' => [
            'max_attempts' => (int)env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int)env('AUTH_LOGIN_DECAY_SECONDS', 60),
        ],
        'register' => [
            'max_attempts' => (int)env('AUTH_REGISTER_MAX_ATTEMPTS', 3),
            'decay_seconds' => (int)env('AUTH_REGISTER_DECAY_SECONDS', 300),
        ],
    ],

];
