<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | All generated classes will use this as root namespace.
    | Example: 'App' or 'Company\\Core'
    |
    */
    'base_namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Paths for generated files (relative to app/)
    |--------------------------------------------------------------------------
    |
    | These are appended after the base namespace physical root (app/).
    | You can freely change these segments to match your project structure.
    |
    */
    'paths' => [
        'repository' => [
            'eloquent'  => 'Repositories/Eloquent',
            'contracts' => 'Repositories/Contracts',
        ],
        'service' => [
            'concretes' => 'Services',
            'contracts' => 'Services/Contracts',
        ],
        'dto'           => 'DTOs',
        'provider'      => 'Providers',
        'controller'    => 'Http/Controllers/Api/V1',
        'resource'      => 'Http/Resources',   // ← قابل‌پیکربندی
        'form_request'  => 'Http/Requests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tests path (relative to project root)
    |--------------------------------------------------------------------------
    */
    'tests' => [
        'feature' => 'tests/Feature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Command-level defaults. CLI flags will override these.
    |
    */
    'defaults' => [
        'with_controller'    => true,
        'with_form_requests' => false,
        'with_unit_test'     => true,
        'with_resource'      => true,
        'with_dto'           => true,
        'with_provider'      => true,
    ],
];
