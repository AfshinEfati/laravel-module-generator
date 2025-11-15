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
        'actions'       => 'Actions',
        'docs'          => 'Docs',
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
        'with_actions'       => false,
        'controller_middleware' => [],
        'controller_type'    => 'web', // 'web' or 'api' - تنظیم نوع کنترلر پیش‌فرض
    ],

    'swagger' => [
        /*
        |----------------------------------------------------------------------
        | Swagger UI Theme
        |----------------------------------------------------------------------
        |
        | Choose from: 'vanilla', 'tailwind', 'dark'
        | - vanilla: Pure CSS, no dependencies (fastest)
        | - tailwind: Tailwind CSS, fully customizable
        | - dark: Dark mode with auto toggle
        |
        */
        'theme' => env('SWAGGER_THEME', 'vanilla'),

        /*
        |----------------------------------------------------------------------
        | Swagger UI Colors (for vanilla and tailwind themes)
        |----------------------------------------------------------------------
        |
        | Customize the color scheme. These are used in CSS variables
        | or Tailwind config depending on theme choice.
        |
        */
        'colors' => [
            'primary' => env('SWAGGER_COLOR_PRIMARY', '#3b82f6'),         // آبی
            'primary_dark' => env('SWAGGER_COLOR_PRIMARY_DARK', '#1e40af'),
            'primary_light' => env('SWAGGER_COLOR_PRIMARY_LIGHT', '#eff6ff'),
            'secondary' => env('SWAGGER_COLOR_SECONDARY', '#06b6d4'),     // فیروزه‌ای
            'success' => env('SWAGGER_COLOR_SUCCESS', '#10b981'),         // سبز
            'warning' => env('SWAGGER_COLOR_WARNING', '#f59e0b'),         // زرد
            'danger' => env('SWAGGER_COLOR_DANGER', '#ef4444'),           // قرمز
            'dark' => env('SWAGGER_COLOR_DARK', '#1f2937'),               // متن تیره
            'light' => env('SWAGGER_COLOR_LIGHT', '#f9fafb'),             // زمینه روشن
            'border' => env('SWAGGER_COLOR_BORDER', '#e5e7eb'),           // خطوط
            'text' => env('SWAGGER_COLOR_TEXT', '#374151'),               // متن
            'text_light' => env('SWAGGER_COLOR_TEXT_LIGHT', '#6b7280'),   // متن کم‌رنگ
        ],

        /*
        |----------------------------------------------------------------------
        | Swagger UI Fonts
        |----------------------------------------------------------------------
        |
        | Customize typography for a more personalized look
        |
        */
        'fonts' => [
            'family' => env('SWAGGER_FONT_FAMILY', 'system-ui, -apple-system, sans-serif'),
            'mono' => env('SWAGGER_FONT_MONO', '"Fira Code", "Courier New", monospace'),
        ],

        /*
        |----------------------------------------------------------------------
        | Dark Mode Settings
        |----------------------------------------------------------------------
        |
        | Used when theme is set to 'dark'
        |
        */
        'dark_mode' => [
            'enabled' => env('SWAGGER_DARK_MODE_ENABLED', true),
            'default' => env('SWAGGER_DARK_MODE_DEFAULT', 'auto'), // 'auto', 'light', 'dark'
            'persist' => env('SWAGGER_DARK_MODE_PERSIST', true),    // Save preference
        ],

        /*
        |----------------------------------------------------------------------
        | Swagger UI Display Options
        |----------------------------------------------------------------------
        */
        'display' => [
            'title' => env('SWAGGER_UI_TITLE', 'API Documentation'),
            'description' => env('SWAGGER_UI_DESCRIPTION', 'REST API Documentation'),
            'show_models' => env('SWAGGER_SHOW_MODELS', true),
            'show_examples' => env('SWAGGER_SHOW_EXAMPLES', true),
            'persist_auth' => env('SWAGGER_PERSIST_AUTH', true), // Remember auth token
        ],

        /*
        |----------------------------------------------------------------------
        | Server Configuration
        |----------------------------------------------------------------------
        */
        'server' => [
            'port' => env('SWAGGER_SERVER_PORT', 8000),
            'host' => env('SWAGGER_SERVER_HOST', 'localhost'),
        ],

        /*
        |----------------------------------------------------------------------
        | Swagger Spec Output
        |----------------------------------------------------------------------
        |
        | Where to save the generated swagger.json file
        |
        */
        'spec' => [
            'path' => env('SWAGGER_SPEC_PATH', 'storage/swagger-ui'),
            'filename' => env('SWAGGER_SPEC_FILENAME', 'swagger.json'),
        ],

        /*
        |----------------------------------------------------------------------
        | Authentication
        |----------------------------------------------------------------------
        */
        'security' => [
            'auth_middleware' => env('SWAGGER_AUTH_MIDDLEWARE', 'auth,auth:api,auth:sanctum'),
            'default' => 'bearerAuth',
            'schemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearer_format' => 'JWT',
                    'description' => 'Pass a valid bearer token retrieved from the authentication endpoint.'
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default logging channel for generated actions
    |--------------------------------------------------------------------------
    */
    'logging_channel' => env('MODULE_GENERATOR_LOG_CHANNEL'),
];
