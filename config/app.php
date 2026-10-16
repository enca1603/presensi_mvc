<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Sistem Presensi Sekolah',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'),
    'key' => $_ENV['APP_KEY'] ?? '',
    'timezone' => $_ENV['TIMEZONE'] ?? 'Asia/Jakarta',
    'upload_max_size' => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 2097152),
    'upload_allowed_mime' => ['image/jpeg', 'image/png', 'image/webp'],
    'upload_allowed_ext' => ['jpg', 'jpeg', 'png', 'webp'],

    'session' => [
        'name' => $_ENV['SESSION_NAME'] ?? 'presensi_session',
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],

    'whatsapp' => [
        'api_url' => $_ENV['WHATSAPP_API_URL'] ?? '',
        'api_key' => $_ENV['WHATSAPP_API_KEY'] ?? '',
        'sender' => $_ENV['WHATSAPP_SENDER'] ?? '',
    ],
];
