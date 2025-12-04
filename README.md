# Laravel Module Generator

[![Docs Deployment Status](https://github.com/AfshinEfati/Laravel-Scaffolder/actions/workflows/docs.yml/badge.svg?branch=main)](https://github.com/AfshinEfati/Laravel-Scaffolder/actions/workflows/docs.yml)

## 📖 Documentation

**Full documentation:** 👉 [Laravel Module Generator Docs](https://afshinefati.github.io/Laravel-Scaffolder/)

---

Generate complete, test-ready Laravel modules from a single Artisan command. The generator scaffolds repositories, services, DTOs, controllers, API resources, form requests, feature tests, and supporting helpers so you can jump straight to business logic.

> **Compatible with Laravel 10 & 11 · Requires PHP 8.1+**

## Why this package?

- **Schema-aware scaffolding** – infer metadata from existing migrations or inline `--fields` definitions to pre-fill DTOs, validation rules, and test payloads.
- **End-to-end module wiring** – repositories, services, and providers are generated together and the provider is auto-registered.
- **API-first controllers** – generate API controllers with form requests and resources by default, or switch to web controllers via config.
- **Action layer support** – optional invokable action classes for clean separation of concerns.
- **Opinionated feature tests** – CRUD tests exercise success and failure flows using inferred field metadata.
- **Jalali date tooling** – built-in `goli()` helper and Carbon macros for Persian calendar support.
- **✨ Built-in API Docs** – generate OpenAPI documentation **without L5-Swagger or any external packages**!
- **Module-scoped requests** – form requests live under `Http/Requests/{Module}` for better organization.

## Requirements

- PHP 8.1 or newer
- Laravel framework 10.x or 11.x
- ✅ **No external dependencies** – API documentation included!

## Installation

Require the package and let the generator mirror its base assets automatically during console boot:

```bash
composer require efati/laravel-scaffolder --dev
```

The service provider copies the default repositories, services, helper, and configuration into your application whenever the package runs in the console, so there is no extra publish command required after installation.

Need to refresh the assets after making manual edits or upgrading? Re-run the publish command and pick the `module-generator` tag to overwrite the files.

```bash
php artisan vendor:publish --provider="Efati\ModuleGenerator\ModuleGeneratorServiceProvider" --tag=module-generator
```

To customise the stub templates used for every generated file, publish the dedicated stubs when you need them:

```bash
php artisan vendor:publish --provider="Efati\ModuleGenerator\ModuleGeneratorServiceProvider" --tag=module-generator-stubs
php artisan vendor:publish --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" --tag=module-generator-stubs
```

This copies the templates to `resources/stubs/module-generator`, letting you adapt method signatures, imports, or formatting to match your house style. Leave them unpublished if the defaults already suit your project.

## Quick start

### Basic API module with form requests and tests

```bash
php artisan make:module Product -a --requests --tests
```

This generates:

- Repository interface + Eloquent implementation
- Service interface + implementation
- DTO class
- API controller with form requests
- API resource
- Feature tests

### With schema metadata from migration

```bash
php artisan make:module Product -a --from-migration=create_products_table
```

### With inline schema (no migration needed)

```bash
php artisan make:module Product -a \
  --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
```

### With action layer

```bash
php artisan make:module Product -a --actions
```

Generates invokable action classes for each CRUD operation (List, Show, Create, Update, Delete).

## Command options

| Option                | Alias       | Description                                                                                                 |
| --------------------- | ----------- | ----------------------------------------------------------------------------------------------------------- |
| `--api`               | `-a`        | Generate API controller with form requests and actions. Automatically enables `--requests` and `--actions`. |
| `--actions`           | –           | Generate invokable action classes for CRUD operations.                                                      |
| `--requests`          | `-r`        | Generate `Store` and `Update` form requests.                                                                |
| `--tests`             | `-t`        | Generate CRUD feature tests.                                                                                |
| `--controller=Subdir` | `-c`        | Place controller in a subfolder (e.g., `Admin`).                                                            |
| `--swagger`           | `-sg`       | Generate OpenAPI documentation in `App\Docs\{Module}Doc`.                                                   |
| `--no-actions`        | –           | Skip action layer generation (opposite of `--actions`).                                                     |
| `--all` / `--full`    | `-a` / `-f` | Generate complete stack: controllers, requests, resources, tests, provider, DTOs, swagger, actions.         |
| `--from-migration=`   | `-fm`       | Infer schema from migration file name or path.                                                              |
| `--fields=`           | –           | Inline schema: `name:string:unique, email:email, price:decimal(10,2)`                                       |
| `--no-controller`     | `-nc`       | Skip controller generation.                                                                                 |
| `--no-resource`       | `-nr`       | Skip API resource generation.                                                                               |
| `--no-dto`            | `-nd`       | Skip DTO generation.                                                                                        |
| `--no-test`           | `-nt`       | Skip feature tests.                                                                                         |
| `--no-provider`       | `-np`       | Skip provider creation.                                                                                     |
| `--no-swagger`        | –           | Disable Swagger generation.                                                                                 |
| `--force`             | `-f`        | Overwrite existing files.                                                                                   |

**Default behavior** can be configured in `config/module-generator.php` under the `defaults` section.

## Schema inference

The generator builds accurate metadata from multiple sources:

- **Migration parsing** – Extract columns, types, nullability, uniqueness, and foreign keys from migration files
- **Inline schema** – Define fields directly: `name:string:unique, price:decimal(10,2), active:boolean`
- **Model inspection** – Fall back to fillable fields and relationships from your Eloquent model

This metadata feeds into DTOs, form requests, resources, and tests automatically.

## Generated files

Each module includes:

- **Repository** – Interface + Eloquent implementation with Criteria pattern support
- **Service** – Business logic layer with interface for dependency injection and dynamic method forwarding
- **DTO** – Data transfer object with type hints, validation, and request conversion helpers
- **Controller** – API or web controller with dependency injection and resource formatting
- **Resource** – API resource for consistent JSON responses with relationship eager loading
- **Form Requests** – Store and Update request classes with auto-generated validation rules
- **Policy** – Authorization policies with standard CRUD gates
- **Feature Tests** – CRUD tests with success/failure scenarios using inferred field metadata
- **Provider** – Auto-registered service provider for bindings and dependency injection setup
- **Actions** (optional) – Invokable action classes for clean CRUD operation encapsulation

## Feature tests

Generate CRUD tests with `--tests`:

```bash
php artisan make:module Product -a --tests
```

Tests include:

- Success and failure scenarios for all CRUD operations
- Auto-generated payloads based on schema metadata
- Validation error assertions
- Foreign key relationship checks

## OpenAPI/Swagger documentation

### ✨ New: Built-in Swagger without external dependencies!

Generate interactive API documentation with **zero external packages**:

```bash
# 1. Initialize Swagger UI
php artisan swagger:init

# 2. Generate documentation from routes
php artisan swagger:generate

# 3. View in browser
php artisan swagger:ui
# Visits: http://localhost:8000/docs
```

**Or integrate with your Laravel app:**

In `routes/api.php`:

```php
use Efati\ModuleGenerator\Traits\RegistersSwaggerRoutes;

Route::middleware(['api'])->group(function () {
    Route::registerSwaggerRoutes(); // Adds /api/docs
    Route::apiResource('products', ProductController::class);
});
```

Visit: `http://localhost:8000/api/docs`

**Features:**

- ✅ No L5-Swagger or Swagger-PHP dependency
- ✅ Beautiful, responsive UI
- ✅ Automatic route scanning
- ✅ OpenAPI 3.0 compliant
- ✅ Fully customizable
- ✅ Production-ready

👉 [Full Swagger Documentation](SWAGGER_NO_DEPENDENCIES.md)

### PHPDoc Annotations (OpenAPI-compatible)

Generate PHPDoc documentation files with `@OA\` annotations:

```bash
# Add Swagger documentation to a module
php artisan make:module Product --swagger

# Or generate documentation for all routes
php artisan make:swagger --force
```

This creates PHPDoc files in `app/Docs/` that are **automatically compatible with optional packages** like `zircote/swagger-php` or `l5-swagger`:

```php
<?php

namespace App\Docs;

/**
 * @OA\Tag(name="Product")
 * @OA\Get(path="/api/products", ...)
 * @OA\Post(path="/api/products", ...)
 */
class ProductDoc { }
```

**Zero dependencies** – works standalone or integrates seamlessly with optional packages.

👉 [PHPDoc Generation Guide](SWAGGER_PHPDOC_GENERATION.md)

## Jalali date support

Built-in `goli()` helper for Persian calendar conversions:

```php
// Convert to Jalali
$jalali = goli(now())->toGoliDateString(); // 1403-07-31

// Parse from Jalali
$gregorian = Goli::parseGoli('1403-01-01 08:30:00', 'Asia/Tehran');
```

Automatically used in generated resources and API responses.

## Configuration

Customize behavior in `config/module-generator.php`:

```php
'defaults' => [
    'controller_type' => 'api',              // 'api' or 'web'
    'with_form_requests' => true,            // Auto-generate form requests
    'with_actions' => true,                  // Auto-generate action classes
    'with_tests' => true,                    // Auto-generate tests
    'with_controller' => true,               // Auto-generate controller
    'with_resource' => true,                 // Auto-generate API resource
    'with_dto' => true,                      // Auto-generate DTO
    'with_provider' => true,                 // Auto-generate service provider
    'with_swagger' => false,                 // Auto-generate Swagger docs
    'controller_middleware' => ['auth:sanctum'], // Applied to all controllers
],

'paths' => [
    'controller' => 'Http/Controllers/Api/V1',  // Controller directory
    'repository' => [
        'contracts' => 'Repositories/Contracts',
        'eloquent' => 'Repositories/Eloquent',
    ],
    'service' => [
        'contracts' => 'Services/Contracts',
        'concretes' => 'Services',
    ],
    'dto' => 'DTOs',
    'actions' => 'Actions',
    'resource' => 'Http/Resources',
    'form_request' => 'Http/Requests',
    'tests' => [
        'feature' => 'tests/Feature',
    ],
    'docs' => 'Docs',
],
```

**Customize stubs:**

```bash
php artisan vendor:publish --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" --tag=module-generator-stubs
```

Edit templates in `resources/stubs/module-generator/` to match your code style.

## Advanced Usage

### Combining multiple options

```bash
# Full-stack API module with everything
php artisan make:module Order -a --requests --tests --swagger --force

# API module with action layer and custom controller folder
php artisan make:module Invoice -a --actions --controller=Admin/Accounting

# Web module with actions but no tests
php artisan make:module BlogPost --controller=Blog --actions --no-test

# Swagger documentation only (no module files)
php artisan make:module Payment --swagger
```

### Using with database migrations

```bash
# Generate migration first
php artisan make:migration create_products_table

# Then generate module with schema inference
php artisan make:module Product -a --from-migration=create_products_table --tests
```

### Dynamic query building in services

Services support dynamic method forwarding to repositories:

```php
// In your service
$products = $this->service->findDynamic(
    where: ['status' => 'active'],
    with: ['category', 'tags'],
    whereIn: ['price_range' => [100, 500]],
    limit: 20
);
```

### Action layer usage in controllers

```php
// Auto-loaded action classes
public function store(CreateProductAction $action, StoreProductRequest $request)
{
    $product = $action(new ProductDTO(...$request->validated()));
    return new ProductResource($product);
}

public function index(ListProductAction $action)
{
    $products = $action();
    return ProductResource::collection($products);
}
```

### Custom field definitions

```bash
php artisan make:module Article \
  --fields="title:string:unique, \
             slug:string:unique, \
             content:text, \
             excerpt:string:nullable, \
             published_at:datetime:nullable, \
             author_id:integer:fk=users.id, \
             view_count:integer:default=0"
```

Supported modifiers: `nullable`, `unique`, `default=value`, `fk=table.column`

## Best Practices

### 1. Use DTOs for validation and type safety

```php
// Always validate through DTO
$dto = ProductDTO::fromRequest($request);
$product = $this->service->store($dto);
```

### 2. Keep business logic in services

```php
// ❌ Don't put logic in controller
public function store(StoreProductRequest $request)
{
    $data = $request->validated();
    $data['slug'] = Str::slug($data['title']);
    return Product::create($data);
}

// ✅ Put logic in service
public function store(mixed $payload): Product
{
    $payload['slug'] = Str::slug($payload['title']);
    return $this->repository->store($payload);
}
```

### 3. Use resources for API responses

```php
// ✅ Format responses consistently
return new ProductResource($product);
return ProductResource::collection($products);
```

### 4. Leverage action classes for complex operations

```php
// Action classes keep code organized and testable
class ComplexExportProductsAction extends BaseAction
{
    public function handle(array $filters): Collection
    {
        // Complex logic here
    }
}
```

### 5. Use form requests for validation

```php
// ✅ Validation happens before controller
// All validated data is type-safe and clean
public function store(StoreProductRequest $request)
{
    return new ProductResource(
        $this->service->store($request->validated())
    );
}
```

## Troubleshooting

### Issue: "Stub file not found"

**Problem:** Generator can't find stub templates.

**Solution:**

```bash
php artisan vendor:publish \
  --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" \
  --tag=module-generator-stubs --force
```

### Issue: "Model not found"

**Problem:** Generator can't locate your Eloquent model.

**Solution:**

- Ensure model exists at `App\Models\{ModuleName}`
- Or specify schema with `--fields` or `--from-migration`

```bash
# Create model first
php artisan make:model Product -m

# Then generate module
php artisan make:module Product -a --from-migration
```

### Issue: Provider not auto-registered

**Problem:** Service provider not added to config/app.php or bootstrap/providers.php

**Solution:**

- Check that `--no-provider` was not used
- Manually add to `bootstrap/providers.php` (Laravel 11):

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ProductServiceProvider::class,  // Add this
];
```

Or to `config/app.php` (Laravel 10):

```php
'providers' => [
    // ...
    App\Providers\ProductServiceProvider::class,
],
```

### Issue: Swagger annotations not showing

**Problem:** OpenAPI docs not rendering in Swagger UI.

**Solution:**

1. Install swagger package:

```bash
composer require zircote/swagger-php
```

2. Regenerate API docs:

```bash
php artisan l5-swagger:generate
```

3. Access at: `http://your-app/api/documentation`

### Issue: Tests fail with database errors

**Problem:** Feature tests can't access database tables.

**Solution:**

- Run migrations: `php artisan migrate:fresh`
- Check test database in `phpunit.xml`
- Ensure test routes are registered in `routes/api.php`:

```php
Route::apiResource('test-products', ProductController::class);
```

## Resources

## License

MIT

---

_پینوشت: با تشکر از gole davoodi 😆_
