# Configuration

Customize the generator defaults so all your team members generate consistent module structures.

## Publishing Configuration

```bash
php artisan vendor:publish \
  --provider="Efati\ModuleGenerator\ModuleGeneratorServiceProvider" \
  --tag=module-generator
```

This creates `config/module-generator.php` with sensible defaults.

## Configuration Options

### Namespace

```php
'namespace' => 'App',  // Root namespace for all generated classes
```

### Paths

```php
'paths' => [
    'controller' => 'Http/Controllers/Api/V1',
    'request' => 'Http/Requests',
    'resource' => 'Http/Resources',
    'repository' => [
        'contracts' => 'Repositories/Contracts',
        'eloquent' => 'Repositories/Eloquent',
    ],
    'service' => [
        'contracts' => 'Services/Contracts',
        'concretes' => 'Services',
    ],
    'dto' => 'DTOs',
    'action' => 'Actions',
    'provider' => 'Providers',
    'tests' => 'tests/Feature',
    'docs' => 'Docs',
],
```

### Defaults

```php
'defaults' => [
    'api' => false,                    // Generate API controller by default
    'requests' => false,               // Generate form requests
    'dto' => true,                     // Generate DTOs
    'resource' => true,                // Generate API Resources
    'repository' => true,              // Generate repositories
    'service' => true,                 // Generate services
    'test' => false,                   // Generate tests
    'actions' => false,                // Generate action layer
    'swagger' => false,                // Generate OpenAPI docs
    'controller_middleware' => [],     // Middleware for controllers (e.g., ['auth:sanctum'])
],
```

### Swagger/OpenAPI

```php
'swagger' => [
    'security_schemes' => [
        'BearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ]
    ],
    'authentication_middleware' => ['auth', 'auth:api', 'auth:sanctum'],
],
```

## Publishing Stubs

Customize templates for all generated files:

```bash
php artisan vendor:publish \
  --provider="Efati\ModuleGenerator\ModuleGeneratorServiceProvider" \
  --tag=module-generator-stubs
```

Creates `resources/stubs/module-generator/` with templates:

- `controller.api.stub` – API controller template
- `controller.web.stub` – Web controller template
- `service.stub` – Service class
- `repository.stub` – Repository class
- `dto.stub` – Data Transfer Object
- `resource.stub` – API Resource
- `request.store.stub` – Store request
- `request.update.stub` – Update request
- `tests/feature.stub` – Feature test template
- `provider.stub` – Service provider template

## Available Placeholders

Use these in stub templates:

- `{{ namespace }}` – Configured namespace
- `{{ modelName }}` – Generated model name (e.g., "Product")
- `{{ modelNamePlural }}` – Plural form (e.g., "Products")
- `{{ tableName }}` – Database table name
- `{{ properties }}` – DTO properties
- `{{ rules }}` – Validation rules
- `{{ relationships }}` – Model relationships
- `{{ fillable }}` – Fillable columns

## Automatic Provider Registration

Generated service providers auto-register in:

- **Laravel 10** – `config/app.php`
- **Laravel 11** – `bootstrap/providers.php`

Ensure the configured provider path exists or enable it in your application before running the generator.

## Environment Variables

```bash
# Force overwrite without --force flag
MODULE_GENERATOR_FORCE_OVERWRITE=true

# Disable test generation globally
MODULE_GENERATOR_DISABLE_TESTS=true

# Custom logging channel for actions
MODULE_GENERATOR_LOG_CHANNEL=module
```

Document these in `.env.example` for new team members.
