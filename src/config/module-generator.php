<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Namespace Configuration
    |--------------------------------------------------------------------------
    */
    'base_namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Paths for generated files (relative to app/)
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'repository'          => 'Repositories/Eloquent',
        'repository_contract' => 'Repositories/Contracts',
        'service'             => 'Services',
        'service_contract'    => 'Services/Contracts',
        'dto'                 => 'DTOs',
        'test'                => 'Tests/Feature',
        'provider'            => 'Providers',
        'controller'          => 'Http/Controllers/Api/V1',
        'form_request'        => 'Http/Requests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatically generate files by default
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'with_controller'    => false,
        'with_form_requests' => false,
        'with_unit_test'     => false,
    ],
];
