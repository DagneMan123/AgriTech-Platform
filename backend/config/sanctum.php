<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:5173,localhost:8080,127.0.0.1,127.0.0.1:3000,127.0.0.1:5173,127.0.0.1:8080',
        env('FRONTEND_URL') ? ',' . parse_url(env('FRONTEND_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web', 'api'],
    'expiration' => null,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'verify_csrf_token' => \App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => \App\Http\Middleware\EncryptCookies::class,
    ],

    'cors' => [
        'paths' => ['api/*', 'sanctum/csrf-cookie'],
        'allowed_methods' => ['*'],
        'allowed_origins' => explode(',', env('SANCTUM_CORS_ORIGINS', sprintf(
            '%s%s',
            'http://localhost:3000,http://localhost:5173,http://localhost:8080,http://127.0.0.1:3000,http://127.0.0.1:5173,http://127.0.0.1:8080,https://localhost',
            env('FRONTEND_URL') ? ',' . env('FRONTEND_URL') : ''
        ))),
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => ['Authorization', 'X-New-Token'],
        'max_age' => 3600,
        'supports_credentials' => true,
    ],

    'token_storage' => env('SANCTUM_TOKEN_STORAGE', 'database'),
    'token_expiry_minutes' => env('SANCTUM_TOKEN_EXPIRY_MINUTES', 0),
    'revoke_previous_tokens' => env('SANCTUM_REVOKE_PREVIOUS_TOKENS', false),

    'model' => \App\Models\PersonalAccessToken::class,
];

