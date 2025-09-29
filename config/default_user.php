<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default User
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default user for your application. This user
    | will be used for quickly logging into the application without needing
    | to create a user manually when in a local development environment.
    |
    */
    'name' => env('DEFAULT_USER_NAME', 'Admin'),
    'email' => env('DEFAULT_USER_EMAIL', 'admin@example.com'),
    'password' => env('DEFAULT_USER_PASSWORD', 'password'),
];
