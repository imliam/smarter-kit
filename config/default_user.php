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

    /*
    |--------------------------------------------------------------------------
    | Prefill Login
    |--------------------------------------------------------------------------
    |
    | This option determines whether the default user login should be prefilled
    | in the login form. If set to true, the login form will automatically
    | populate the email field with the default user's email.
    |
    */
    'prefill_login' => env('DEFAULT_USER_PREFILL_LOGIN', true),
];
