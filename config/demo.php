<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | When demo mode is enabled, login forms will display demo credentials
    | with auto-fill functionality. This is useful for showcasing the
    | application to potential clients or for testing purposes.
    |
    */

    'enabled' => env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Demo Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials will be displayed on the login forms when demo mode
    | is enabled. Make sure these accounts exist in your database.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Password policy requires every demo credential to contain at least one
    | upper-case letter, one lower-case letter, one number and one symbol.
    | Defaults below match the values written by the Doctor / Patient seeders
    | (Modules\AdminManagement\database\seeders\DoctorSeeder) and MUST be
    | overridden via the .env file before deploying to any shared environment.
    |--------------------------------------------------------------------------
    */

    'credentials' => [
        'doctor' => [
            'email' => env('DEMO_DOCTOR_EMAIL', 'doctor@demo.com'),
            'password' => env('DEMO_DOCTOR_PASSWORD', 'Doctor@2026!'),
        ],
        'patient' => [
            'email' => env('DEMO_PATIENT_EMAIL', 'patient@demo.com'),
            'password' => env('DEMO_PATIENT_PASSWORD', 'Patient@2026!'),
        ],
        'admin' => [
            'email' => env('DEMO_ADMIN_EMAIL', 'admin@demo.com'),
            'password' => env('DEMO_ADMIN_PASSWORD', 'Admin@2026!'),
        ],
    ],
];
