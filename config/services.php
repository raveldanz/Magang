<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    // Direktori sekolah nasional (NPSN) untuk sinkronisasi master sekolah dan pencarian langsung.
    'school_directory' => [
        'enabled' => env('SCHOOL_DIRECTORY_ENABLED', true),
        'base_url' => env('SCHOOL_DIRECTORY_URL', 'https://api-sekolah-indonesia.vercel.app'),
        'timeout' => (int) env('SCHOOL_DIRECTORY_TIMEOUT', 15),
        // PHP Windows sering tanpa curl.cainfo. Bawaan: bundel sertifikat Mozilla yang ikut proyek
        // (resources/certs/cacert.pem), sehingga zip demo di PC mana pun tetap bisa memverifikasi SSL.
        // Isi SCHOOL_DIRECTORY_CA_BUNDLE hanya bila jaringan kantor memakai sertifikat proxy sendiri.
        'ca_bundle' => env('SCHOOL_DIRECTORY_CA_BUNDLE') ?: resource_path('certs/cacert.pem'),
    ],

    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],

];
