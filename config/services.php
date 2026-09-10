<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        // Socialite needs an absolute URL here — fall back to building one from
        // APP_URL rather than a bare path, in case GOOGLE_REDIRECT_URI is unset.
        // Uses env() directly (not the url() helper): this file is evaluated
        // during `config:cache`, outside any HTTP request context.
        'redirect' => env('GOOGLE_REDIRECT_URI') ?: rtrim(env('APP_URL', 'http://localhost'), '/') . '/auth/google/callback',
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
    ],

    // Only used by the one-off `destinations:fetch-photos` command, run locally
    // to populate public/img/destinations/ — production never calls Pexels.
    'pexels' => [
        'api_key' => env('PEXELS_API_KEY'),
    ],

];
