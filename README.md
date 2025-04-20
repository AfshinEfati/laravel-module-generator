# Laravel Module Generator

A clean and extensible Laravel module generator designed for API-based projects.

Generates Repository, Service, Interfaces, DTOs, Tests, Service Providers, Controllers, and Form Requests based on best practices.

---

## 📦 Installation

```bash
composer require efati/laravel-module-generator --dev
```

(Optional) To publish base classes and config:

```bash
php artisan vendor:publish --tag=module-generator
```

---

## 🚀 Usage

```bash
php artisan make:module Company
```

This will generate:

- `Repositories/Eloquent/CompanyRepository.php`
- `Repositories/Contracts/CompanyRepositoryInterface.php`
- `Services/CompanyService.php`
- `Services/Contracts/CompanyServiceInterface.php`
- `DTOs/CompanyDTO.php` (auto-filled from model)
- `Providers/CompanyServiceProvider.php`
- `Tests/Feature/CompanyTest.php`

> You must have a model named `Company` before running the command.

---

## ⚙️ Options

### 1. `--with-controller=Subfolder`

Generates a controller in a subdirectory inside the configured base path (e.g., `Api/V1/Subfolder`).

- Uses the base path from config:  
  `controller` => `Http/Controllers/Api/V1`

**Example:**

```bash
php artisan make:module Flight --with-controller=Admin
```

Output:

```
app/Http/Controllers/Api/V1/Admin/FlightController.php
```

---

### 2. `--api`

Generates an API resource controller instead of a basic one.

**Example:**

```bash
php artisan make:module Flight --with-controller=Admin --api
```

Creates methods:

- `index()`
- `store(Request $request)`
- `show(Flight $flight)`
- `update(Request $request, Flight $flight)`
- `destroy(Flight $flight)`

Also injects service in the constructor:

```php
public function __construct(public FlightService $flightService) {}
```

---

### 3. `--with-form-requests`

Generates `Store` and `Update` FormRequest classes.

**Example:**

```bash
php artisan make:module Flight --with-form-requests
```

Output:

```
app/Http/Requests/Flight/StoreFlightRequest.php
app/Http/Requests/Flight/UpdateFlightRequest.php
```

---

## 📂 Base Classes

Use `vendor:publish` to copy these reusable classes:

- `BaseRepository`
- `BaseRepositoryInterface`
- `BaseService`
- `BaseServiceInterface`
- `config/module-generator.php`

These provide common logic for reuse across all modules and allow customizing path/structure.

---

## 🧪 Requirements

- Laravel 11+
- PHP 8.1+
- Model must exist and define `$fillable` for DTO generation

---

## 🧑‍💻 Author

Made with ❤️ by [Afshin](https://github.com/AfshinEfati)
