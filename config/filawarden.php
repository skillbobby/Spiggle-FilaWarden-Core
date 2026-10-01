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

    'pro_upgrade_url' => 'https://spiggle.dev/filawarden-pro',
];
