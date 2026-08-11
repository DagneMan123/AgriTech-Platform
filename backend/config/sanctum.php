<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sanctum Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies that are typically used by SPAs. You should
    | include the host of your frontend application here.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:5173,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Expiration
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do not
    | expire. This won't tweak the lifetime of first-party session cookies.
    |
    */

    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', null),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Prefix
    |--------------------------------------------------------------------------
    |
    | This value controls the token prefix. By default the API tokens will be
    | prefixed with "Bearer" followed by the token hash.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be applied to every request to authenticate
    | requests with Sanctum. You should not edit these directly.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];
