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

### Options

| Option                | Description |
|-----------------------|-------------|
| `--api`               | Generate an API Resource controller |
| `--controller=Subdir` | Place controller in a specific subfolder |
| `--requests`          | Generate Form Requests |
| `--tests`             | Generate CRUD Feature Tests |
| `--dto`               | Generate DTO class for the module |
| `--force`             | Overwrite existing files |

---

### Example

```bash
php artisan make:module Product --api --requests --controller=Admin --tests --dto
```

This will generate:

- **Model**: `app/Models/Product.php`
- **Repository**: `app/Repositories/ProductRepository.php`
- **Repository Interface**: `app/Repositories/Interfaces/ProductRepositoryInterface.php`
- **Service**: `app/Services/ProductService.php`
- **Service Interface**: `app/Services/Interfaces/ProductServiceInterface.php`
- **DTO**: `app/DTOs/ProductDTO.php`
- **Controller**: `app/Http/Controllers/Api/V1/Admin/ProductController.php`
- **Form Requests**: `app/Http/Requests/Product/StoreRequest.php` & `UpdateRequest.php`
- **Feature Tests**: `tests/Feature/ProductTest.php`

---

## Test Generation

When using `--tests`, the package will:

- Use the database connection defined in `.env`
- Run CRUD operations with **valid data** and **invalid data** scenarios
- Test for `404 Not Found` when accessing non-existent records
- Test validation errors from Form Requests
- Test successful creation, update, and deletion

---

## Version History

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
