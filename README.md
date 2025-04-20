# Laravel Module Generator

A clean and extensible Laravel module generator designed for API-based projects.

Generates Repository, Service, Interfaces, DTOs, Tests, and Service Providers based on best practices.

---

## 📦 Installation

```bash
composer require efati/laravel-module-generator --dev
```

(Optional) To publish base classes:

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

You must have a model named `Company` before running the command.

---

## 📂 Base Classes

Use `vendor:publish` to copy these reusable classes:

- `BaseRepository`
- `BaseRepositoryInterface`
- `BaseService`
- `BaseServiceInterface`
- `config/module-generator`

These provide common logic for reuse across all modules.

---

## 💡 Example

To generate a module for `Flight`:

```bash
php artisan make:module Flight
```

---

## 🧪 Requirements

- Laravel 11+
- PHP 8.1+
- Model must exist and define `$fillable` for DTO generation

---

## 🧑‍💻 Author

Made with ❤️ by [Afshin](https://github.com/AfshinEfati)

