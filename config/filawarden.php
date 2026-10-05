<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FilaWarden Operations Intelligence Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('FILAWARDEN_ENABLED', true),

    'navigation' => [
        'group' => 'Operations Intelligence',
        'sort' => 1,
        'icon' => 'heroicon-o-shield-check',
    ],

    /*
    |--------------------------------------------------------------------------
    | Page & Action Authorization
    |--------------------------------------------------------------------------
    | Configure who can access FilaWarden diagnostic pages in production.
    */
    'authorization' => [
        'enforce_in_production' => env('FILAWARDEN_ENFORCE_AUTH', true),
        'permission' => env('FILAWARDEN_PERMISSION', 'access_filawarden'),
        'callback' => null, // fn ($user) => $user->is_super_admin
    ],

    'thresholds' => [
        'cpu' => [
            'warning' => 70, // %
            'critical' => 90, // %
        ],
        'memory' => [
            'warning' => 75, // %
            'critical' => 90, // %
        ],
        'disk' => [
            'warning' => 80, // %
            'critical' => 95, // %
        ],
        'load' => [
            'warning' => 2.0,
            'critical' => 4.0,
        ],
    ],

    'cache' => [
        'telemetry_ttl' => 10, // seconds
        'audit_ttl' => 60, // seconds
    ],

    'max_scanned_files' => env('FILAWARDEN_MAX_SCANNED_FILES', 500),

    'log_path' => env('FILAWARDEN_LOG_PATH', null),

    'pro_upgrade_url' => 'https://spiggle.dev/filawarden-pro',
];
