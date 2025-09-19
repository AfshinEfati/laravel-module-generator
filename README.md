# Laravel Module Generator

A Laravel package to generate fully structured modules (Model, Repository, Service, Interface, DTO, Controller, Form Requests, and Tests) with a single Artisan command.

## Features

- Generate **Model**, **Repository**, **Service**, **Interface**, **DTO**, **Controller**, **Form Requests**, and **Tests** in one command
- Supports **API Resource** controllers
- Dynamic namespace and path configuration via `config/module-generator.php`
- Automatic binding of Repository and Service in Service Providers
- Generates CRUD Feature Tests with both success and failure scenarios
- Respects your existing `.env` database configuration for tests (no forced SQLite)
- Ability to override stubs
- Compatible with Laravel 10+ and Laravel 11
- Built-in `verta()` helper for Jalali date handling (no external dependency)

---

## Installation

```bash
composer require efati/laravel-module-generator
```

---

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=module-generator-config
```

This will create `config/module-generator.php` where you can adjust:

- **Namespace paths** for models, repositories, services, controllers, DTOs, and tests
- **Default controller path** (e.g., `App\Http\Controllers\Api\V1`)
- **Enable/Disable** generation of tests, form requests, DTOs, etc.

---

## Usage

### Create a module

```bash
php artisan make:module ModelName [options]
```

### Options (long form)

| Option                | Description |
|-----------------------|-------------|
| `--api`               | Generate an API-flavoured controller (default path: `Http/Controllers/Api/V1`) |
| `--controller=Subdir` | Place controller inside a subdirectory (forces controller generation) |
| `--requests`          | Generate Form Requests for Store/Update |
| `--tests`             | Force CRUD Feature Test generation |
| `--no-controller`     | Skip controller generation |
| `--no-resource`       | Skip API Resource generation |
| `--no-dto`            | Skip DTO generation |
| `--no-test`           | Skip Feature Test generation |
| `--no-provider`       | Skip provider creation and auto-registration |
| `--force`             | Overwrite existing files (default is to skip and warn) |

### Short aliases

| Alias | Long option         |
|-------|---------------------|
| `-a`  | `--api`             |
| `-c`  | `--controller`      |
| `-r`  | `--requests`        |
| `-t`  | `--tests`           |
| `-nc` | `--no-controller`   |
| `-nr` | `--no-resource`     |
| `-nd` | `--no-dto`          |
| `-nt` | `--no-test`         |
| `-np` | `--no-provider`     |
| `-f`  | `--force`           |

---

### Example

```bash
php artisan make:module Product --api --requests --controller=Admin --tests
```

This will generate:

- **Model**: `app/Models/Product.php`
- **Repository**: `app/Repositories/Eloquent/ProductRepository.php`
- **Repository Interface**: `app/Repositories/Contracts/ProductRepositoryInterface.php`
- **Service**: `app/Services/ProductService.php`
- **Service Interface**: `app/Services/Contracts/ProductServiceInterface.php`
- **DTO**: `app/DTOs/ProductDTO.php`
- **Controller**: `app/Http/Controllers/Api/V1/Admin/ProductController.php`
- **Form Requests**: `app/Http/Requests/StoreProductRequest.php` & `UpdateProductRequest.php`
- **Feature Tests**: `tests/Feature/ProductCrudTest.php`

> Tip: rerunning the generator without `--force` will skip existing files and list the skipped paths in the console output.

---

## Test Generation

When using `--tests`, the package will:

- Use the database connection defined in `.env`
- Run CRUD operations with **valid data** and **invalid data** scenarios
- Test for `404 Not Found` when accessing non-existent records
- Test validation errors from Form Requests
- Test successful creation, update, and deletion

---

## Jalali Date Helper

Starting from this release the package ships with an in-house implementation of the popular `verta()` helper. You no longer
need a third-party dependency to work with Jalali dates in the generated modules.

```php
use Efati\ModuleGenerator\Support\Verta;

// via the global helper
$jalali = verta(now())->format('Y/m/d');

// or interacting with the class directly
$jalali = Verta::instance('2024-03-20 12:00:00')->toJalaliDateString();
```

The helper exposes date conversion utilities, Persian digit formatting, timezone awareness, and first-class Carbon
interoperability right out of the box.

---

## Version History

### **v5.3**
- Added short CLI aliases (`-a`, `-c`, `-r`, `-t`, etc.) for faster module generation.
- Introduced safe overwrite behaviour: generators skip existing files unless `--force/-f` is provided.
- Controllers now adapt to API vs. web mode, respecting DTO/resource toggles and returning sensible payloads when those artefacts are disabled.
- Services fall back to array payloads when DTOs are skipped and can work without provider bindings when `--no-provider` is used.

### **v5.2**
- Added **full CRUD Feature Tests** with success & failure cases
- Removed forced SQLite in tests (uses `.env` database settings)
- Fixed **Form Requests** generation bug
- Improved DTO integration in Controllers & Services

### v5.1
- Bug fixes for Form Requests generation
- Improved path handling in configuration

### v5.0
- Major refactor for Laravel 11 support
- Dynamic namespace handling
- Service & Repository auto-binding

---

## License

MIT
