<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'secret' => env('MAILGUN_SECRET'),
        'domain' => env('MAILGUN_DOMAIN'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // SMS Service Configuration
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'nexmo'), // nexmo, twilio, africastalking
        'enabled' => env('SMS_ENABLED', false),
    ],

    'nexmo' => [
        'key' => env('NEXMO_KEY'),
        'secret' => env('NEXMO_SECRET'),
        'from' => env('NEXMO_FROM', 'AgriTech'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'africastalking' => [
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'username' => env('AFRICASTALKING_USERNAME'),
        'from' => env('AFRICASTALKING_FROM', 'AgriTech'),
    ],

    // Payment Gateway Configuration
    'payment' => [
        'provider' => env('PAYMENT_PROVIDER', 'stripe'), // stripe, paypal, telebirr
        'enabled' => env('PAYMENT_ENABLED', false),
    ],

    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox, live
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],

    'telebirr' => [
        'merchant_id' => env('TELEBIRR_MERCHANT_ID'),
        'api_key' => env('TELEBIRR_API_KEY'),
        'base_url' => env('TELEBIRR_BASE_URL', 'https://telebirr.ericsson.net'),
    ],

    // Email Configuration
    'email' => [
        'noreply' => env('MAIL_FROM_ADDRESS', 'noreply@agritech.local'),
        'support' => env('SUPPORT_EMAIL', 'support@agritech.local'),
        'admin' => env('ADMIN_EMAIL', 'admin@agritech.local'),
    ],

    // Storage Configuration
    'storage' => [
        'disk' => env('STORAGE_DISK', 'local'),
        'url_base' => env('STORAGE_URL', '/storage'),
    ],

    // File Upload Configuration
    'uploads' => [
        'max_size_mb' => env('MAX_UPLOAD_SIZE', 10),
        'allowed_extensions' => explode(',', env('ALLOWED_UPLOAD_EXTENSIONS', 'jpg,jpeg,png,pdf,doc,docx')),
        'path' => 'uploads',
    ],

    // Image Configuration
    'images' => [
        'thumbnail_width' => env('THUMBNAIL_WIDTH', 150),
        'thumbnail_height' => env('THUMBNAIL_HEIGHT', 150),
        'medium_width' => env('MEDIUM_WIDTH', 400),
        'medium_height' => env('MEDIUM_HEIGHT', 400),
        'large_width' => env('LARGE_WIDTH', 800),
        'large_height' => env('LARGE_HEIGHT', 800),
    ],

    // Notification Configuration
    'notifications' => [
        'enabled' => env('NOTIFICATIONS_ENABLED', true),
        'channels' => explode(',', env('NOTIFICATION_CHANNELS', 'database,mail,sms')),
    ],

    // Map and Location Configuration
    'map' => [
        'provider' => env('MAP_PROVIDER', 'google'), // google, openstreetmap
        'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'mapbox_token' => env('MAPBOX_TOKEN'),
    ],

    // Cache Configuration
    'cache' => [
        'weather_ttl' => env('CACHE_WEATHER_TTL', 3600),
        'market_prices_ttl' => env('CACHE_MARKET_PRICES_TTL', 3600),
        'user_ttl' => env('CACHE_USER_TTL', 3600),
    ],

    // Report Configuration
    'reports' => [
        'max_records' => env('REPORT_MAX_RECORDS', 10000),
        'export_formats' => explode(',', env('REPORT_EXPORT_FORMATS', 'csv,pdf,excel')),
    ],
];
