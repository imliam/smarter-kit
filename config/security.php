<?php

declare(strict_types=1);

return [
    'hsts' => [
        'enabled' => (bool) env('SECURITY_HSTS_ENABLED', true),
        'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
        'include_subdomains' => (bool) env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => (bool) env('SECURITY_HSTS_PRELOAD', false),
    ],

    'contact_email' => env('SECURITY_CONTACT_EMAIL', 'security@example.com'),

    'gpc_last_update' => env('SECURITY_GPC_LAST_UPDATE'),

    'trusted_proxies' => env('TRUSTED_PROXIES', '*'),

    'trusted_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => mb_trim($host),
        explode(',', (string) env('TRUSTED_HOSTS', '')),
    ))),
];
