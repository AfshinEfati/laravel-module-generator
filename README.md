# Laravel Module Generator

> Latest release: **v6.2.4**

[![Docs Deployment Status](https://github.com/efati/laravel-module-generator/actions/workflows/docs.yml/badge.svg?branch=main)](https://github.com/efati/laravel-module-generator/actions/workflows/docs.yml)

A Laravel package to generate fully structured modules (Model, Repository, Service, Interface, DTO, Controller, Form Requests, and Tests) with a single Artisan command.

## Features

- Generate **Repository**, **Service**, **Interfaces**, **DTO**, **Controller**, **API Resource**, **Form Requests**, and **CRUD Feature Tests** from a single `make:module` command.
- Prime scaffolding metadata from existing migrations or inline `--fields` definitions, letting the generators infer fillable attributes, validation rules, casts, and eager-loadable relations before the Eloquent model exists.
- Automatic binding of repositories/services, optional DTO pipelines, and API resource responses that normalise booleans and date fields through the bundled status helper.
- Form Requests that convert `unique:` pipe rules into `Rule::unique(...)->ignore()` lookups during updates so validation plays nicely with route-model binding.
- Generates CRUD Feature Tests that reuse your `.env` database connection instead of forcing SQLite and cover happy-path plus failure scenarios.
- Publishable configuration & stubs to align namespaces, folder structure, and code style with your project conventions.
- Compatible with Laravel 10 and Laravel 11 out of the box.


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

### Customising generator stubs

If your team follows specific coding standards you can publish and edit the generator stubs:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

All stub files will be copied to `resources/stubs/module-generator`. The generators always look for a published stub first, so
any changes you make there (naming conventions, imports, method bodies, docblocks, etc.) will be reflected in the next
generation run. The package uses simple placeholders such as `{{ namespace }}`, `{{ class }}` or `{{ store_argument }}` inside
the stub files—leave these intact and only change the surrounding structure to keep the dynamic parts working.

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
| `--from-migration=`   | Provide a migration path or keyword to infer fields when the model class does not exist yet |
| `--force`             | Overwrite existing files (default is to skip and warn) |
| `--fields=`           | Inline schema definition so generators can infer fillable fields, validation rules, and test payloads before the Eloquent model exists |

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
| `-fm` | `--from-migration`  |
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

> New in v4: you can prime the generator with a migration when the Eloquent model class is not ready yet:
>
> ```bash
> php artisan make:module Product --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
> ```
>
> The command will scan the migration, infer columns, nullable/unique flags, and foreign keys, then feed that metadata to the DTO, FormRequest, Resource, and Feature Test generators.


---

## Test Generation

When using `--tests`, the package will:

- Use the database connection defined in `.env`
- Run CRUD operations with **valid data** and **invalid data** scenarios
- Test for `404 Not Found` when accessing non-existent records
- Test validation errors from Form Requests
- Test successful creation, update, and deletion

---

## Goli Date Helper

The package now ships with an in-house Jalali toolkit exposed via the `goli()` helper and the `Goli` class, so you can
drop the third-party Verta dependency and reuse the converter anywhere in your project.

```php
use Efati\ModuleGenerator\Support\Goli;

// via the global helper autoloaded by composer
$goli = goli(now())->format('Y/m/d');

// resolving an instance directly or from the service container binding
$goli = Goli::instance('2024-03-20 12:00:00')->toJalaliDateString();
$resolved = app(Goli::class, ['datetime' => now()]);

// human readable differences with optional Persian digits
$diff = goli('2024-03-20')->diffForHumans();          // "5 روز پیش"
$diffFuture = goli('2025-03-20')->diffForHumans(null, true); // "۱ سال بعد"
```

Key capabilities include:

- parsing Jalali date strings (with optional Persian/Arabic digits) via `goli()` or `Goli::parseJalali()`
- converting Jalali dates to Gregorian and vice versa, including array helpers and timezone awareness
- formatting output with automatic Persian digit localisation when desired
- seamless Carbon interoperability for chained date operations
- resolving new instances through the Laravel container using the `goli` binding

### Carbon Jalali Macros

When the service provider boots it registers two Carbon macros, giving you an instant bridge between `Carbon` and `Goli`:

```php
use Carbon\Carbon;

$jalaliNow = Carbon::now('Asia/Tehran')->toJalali();
echo $jalaliNow->format('Y/m/d H:i'); // 1402/12/29 16:45 for example output

$gregorian = Carbon::fromJalali('1403/01/01 08:30:00', 'Asia/Tehran');
echo $gregorian->format('Y-m-d H:i'); // 2024-03-20 08:30
```

The `toJalali()` macro returns a `Goli` instance, so you keep access to all Jalali helpers (digit localisation, formatting helpers, etc.).
`fromJalali()` gives you a regular `Carbon` instance back for further chaining. Both macros accept an optional timezone argument and are only registered once, so you can safely call the service provider multiple times (or invoke `ModuleGeneratorServiceProvider::registerCarbonMacros()` manually in a console script).

> Looking for a quick smoke test? Run `php tests/CarbonMacrosExample.php` to execute the same round-trip conversion showcased above.


---

## Release Checklist

- [ ] Update `CHABELOG.md` with the latest changes.
- [ ] Update docs.
- [ ] Bump the release information in `version.md` if needed.
- [ ] Create and push the release tag.

---

## Version History

### **v6.2.4**
- Update Form Request generation to translate `unique:` pipe rules into `Rule::unique(...)->ignore()` calls, preventing false positives when editing existing records through route-model binding.
- Normalise generated validation arrays so custom Rule instances are preserved alongside classic pipe notation.
- Refined API resource output to continue formatting booleans and timestamps through the status helper utilities.

### **v6.1.0**
- Resource generation now inspects both migrations and model relations to eager-load nested resources automatically.
- Inline schema definitions passed via `--fields` are promoted into fillable arrays, casts, and relationship metadata for downstream generators.

### **v6.0.0**
- Migration introspection was expanded to capture nullable/unique flags, enum values, and foreign key metadata so repositories, DTOs, resources, and tests share a consistent contract.
- Added CLI ergonomics including inline schema parsing, granular opt-in/out flags for controllers/resources/tests/DTOs, and smarter fallbacks when a model class is missing.

### **v5.x**
- Short CLI aliases, safer overwrite behaviour, API-aware controllers, and optional provider/DTO pipelines.
- Feature test scaffolding with success & failure cases, `.env` database reuse, and DTO/Form Request integration improvements.

---

## License

MIT

---

_پینوشت: با تشکر از gole davoodi 😆_
