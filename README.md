# Laravel Module Generator

[![Docs Deployment Status](https://github.com/AfshinEfati/laravel-module-generator/actions/workflows/docs.yml/badge.svg?branch=main)](https://github.com/AfshinEfati/laravel-module-generator/actions/workflows/docs.yml)

## 📖 Documentation

**Full documentation:** 👉 [Laravel Module Generator Docs](https://afshinefati.github.io/laravel-module-generator/)

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
- **OpenAPI documentation** – generate Swagger annotations with `--swagger` flag.
- **Module-scoped requests** – form requests live under `Http/Requests/{Module}` for better organization.

## Requirements

- PHP 8.1 or newer
- Laravel framework 10.x or 11.x
- (Optional for `--swagger`) Install `darkaonline/l5-swagger` **or** `zircote/swagger-php` so OpenAPI annotations can be generated without warnings.

## Installation

Require the package and let the generator mirror its base assets automatically during console boot:

```bash
composer require efati/laravel-module-generator
```

The service provider copies the default repositories, services, helper, and configuration into your application whenever the package runs in the console, so there is no extra publish command required after installation.

Need to refresh the assets after making manual edits or upgrading? Re-run the publish command and pick the `module-generator` tag to overwrite the files.

```bash
php artisan vendor:publish --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" --tag=module-generator
```

To customise the stub templates used for every generated file, publish the dedicated stubs when you need them:

```bash
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

| Option | Alias | Description |
| --- | --- | --- |
| `--api` | `-a` | Generate API controller with form requests and actions. Automatically enables `--requests` and `--actions`. |
| `--actions` | – | Generate invokable action classes for CRUD operations. |
| `--requests` | `-r` | Generate `Store` and `Update` form requests. |
| `--tests` | `-t` | Generate CRUD feature tests. |
| `--controller=Subdir` | `-c` | Place controller in a subfolder (e.g., `Admin`). |
| `--swagger` | `-sg` | Generate OpenAPI documentation in `App\Docs\{Module}Doc`. |
| `--from-migration=` | `-fm` | Infer schema from migration file name or path. |
| `--fields=` | – | Inline schema: `name:string:unique, email:email, price:decimal(10,2)` |
| `--no-controller` | `-nc` | Skip controller generation. |
| `--no-resource` | `-nr` | Skip API resource generation. |
| `--no-dto` | `-nd` | Skip DTO generation. |
| `--no-test` | `-nt` | Skip feature tests. |
| `--no-provider` | `-np` | Skip provider creation. |
| `--no-swagger` | – | Disable Swagger generation. |
| `--force` | `-f` | Overwrite existing files. |

**Default behavior** can be configured in `config/module-generator.php` under the `defaults` section.

## Schema inference

The generator builds accurate metadata from multiple sources:

- **Migration parsing** – Extract columns, types, nullability, uniqueness, and foreign keys from migration files
- **Inline schema** – Define fields directly: `name:string:unique, price:decimal(10,2), active:boolean`
- **Model inspection** – Fall back to fillable fields and relationships from your Eloquent model

This metadata feeds into DTOs, form requests, resources, and tests automatically.

## Generated files

Each module includes:

- **Repository** – Interface + Eloquent implementation for data access
- **Service** – Business logic layer with interface for dependency injection
- **DTO** – Data transfer object with type hints and validation
- **Controller** – API or web controller with dependency injection
- **Resource** – API resource for consistent JSON responses
- **Form Requests** – Store and Update request classes with validation rules
- **Feature Tests** – CRUD tests with success/failure scenarios
- **Provider** – Auto-registered service provider for bindings

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

Generate OpenAPI documentation with the `--swagger` flag:

```bash
# Generate module with Swagger docs
php artisan make:module Product -a --swagger

# Generate only Swagger docs (no module files)
php artisan make:module Product --swagger
```

Documentation is generated in `App\Docs\{Module}Doc` with `@OA` annotations.

**Note:** Requires `darkaonline/l5-swagger` or `zircote/swagger-php` package.

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
    'controller_type' => 'api',        // 'api' or 'web'
    'with_form_requests' => true,      // Auto-generate form requests
    'with_actions' => true,            // Auto-generate action classes
    'with_tests' => true,              // Auto-generate tests
    'controller_middleware' => ['auth:sanctum'], // Applied to all controllers
],
```

**Customize stubs:**

```bash
php artisan vendor:publish --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" --tag=module-generator-stubs
```

Edit templates in `resources/stubs/module-generator/` to match your code style.

## Resources

- [Full documentation](https://afshinefati.github.io/laravel-module-generator/)
- [Changelog](CHABELOG.md)

## License

MIT

---

_پینوشت: با تشکر از gole davoodi 😆_
