# Laravel Module Generator

[![Docs Deployment Status](https://github.com/efati/laravel-module-generator/actions/workflows/docs.yml/badge.svg?branch=main)](https://github.com/efati/laravel-module-generator/actions/workflows/docs.yml)

Generate complete, test-ready Laravel modules from a single Artisan command. The generator scaffolds models, repositories, services, interfaces, DTOs, controllers, API resources, form requests, feature tests, and supporting helpers so you can jump straight to business logic.

> **Compatible with Laravel 10 & 11 · Requires PHP 8.1+ · Latest release: v6.2.4**

## معرفی کوتاه

Laravel Module Generator در چند ثانیه یک «ماژول کامل» شامل مدل، مخزن، سرویس، اینترفیس، DTO، کنترلر (وب یا API)، ریسورس، فرم‌ریکوئست و تست فیچر می‌سازد. به کمک تنظیمات دلخواه و استاب‌های قابل سفارشی‌سازی می‌توانید ساختار پروژه را مطابق الگوهای تیم خود نگه دارید.

### امکانات کلیدی

- تحلیل مایگریشن‌ها یا اسکیمای درون‌خطی (`--fields`) برای استخراج خودکار فیلدها، قوانین اعتبارسنجی و متادیتا.
- تولید همزمان Repository، Service و Service Provider با اتصال خودکار به کانتینر برنامه.
- خروجی API استاندارد با `StatusHelper` و پشتیبانی از تاریخ جلالی به کمک `goli()`.
- تست‌های فیچر CRUD با سناریوهای موفق و ناموفق که از همان متادیتای DTO/FormRequest استفاده می‌کنند.
- پشتیبانی از شخصی‌سازی کامل مسیرها، نام‌فضاها، استاب‌ها و رفتار پیش‌فرض از طریق `config/module-generator.php`.

### شروع سریع

1. بسته را نصب کنید: `composer require efati/laravel-module-generator`
2. کلاس‌های پایه و فایل پیکربندی را منتشر کنید: `php artisan vendor:publish --tag=module-generator`
3. (اختیاری) استاب‌ها را برای شخصی‌سازی کپی کنید: `php artisan vendor:publish --tag=module-generator-stubs`
4. اولین ماژول را بسازید:

   ```bash
   php artisan make:module Product \
     --api --requests --tests \
     --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
   ```

   اگر هنوز مایگریشنی وجود ندارد، از DSL درون‌خطی استفاده کنید:

   ```bash
   php artisan make:module Product --api --requests --tests \
     --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
   ```

### تغییرات مهم نسخه 6.2.4

- پشتیبانی کامل از nullable/unique/foreign-key در `--fields` و همگام‌سازی آن با DTO/Resource/Test.
- بهبود تحلیل مایگریشن‌ها برای استخراج روابط، enumها و قوانین اعتبارسنجی.
- ثبت خودکار Service Provider در `bootstrap/providers.php` (لاراول 11) یا `config/app.php`.
- استانداردسازی خروجی Resource با `StatusHelper` و ابزار Jalali `goli()`.

---

## Why this package?

- **Schema-aware scaffolding** – infer metadata from existing migrations or inline `--fields` definitions to pre-fill DTOs, validation rules, casts, and test payloads.
- **End-to-end module wiring** – repositories, services, and providers are generated together and the provider is auto-registered in your application bootstrap or `config/app.php`.
- **Resource-rich APIs out of the box** – controllers, resources, and the bundled `StatusHelper` format dates, booleans, and relations consistently using the Jalali-aware `Goli` helper.
- **Opinionated feature tests** – optional CRUD tests exercise success and failure flows using the metadata gathered from migrations or schema definitions.
- **First-class Jalali tooling** – the service provider binds the `goli()` helper and Carbon macros so Persian calendars are available anywhere in your app without external packages.

## Requirements

- PHP 8.1 or newer
- Laravel framework 10.x or 11.x

## Installation

Require the package and publish the base assets:

```bash
composer require efati/laravel-module-generator
php artisan vendor:publish --tag=module-generator
```

Publishing with the `module-generator` tag installs base repository/service classes, the `StatusHelper`, and `config/module-generator.php` where you can adjust namespaces, paths, and feature toggles.

To customise the stub templates used for every generated file, publish the dedicated stubs once:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

The stubs will be copied to `resources/stubs/module-generator`, allowing you to adapt method signatures, imports, or formatting to match your house style.

## Quick start

Generate a fully wired Product module that targets your API stack, infers validation rules from an existing migration, and creates feature tests:

```bash
php artisan make:module Product --api --requests --tests --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
```

No migration yet? Prime the generator with inline schema metadata instead:

```bash
php artisan make:module Product --api --requests --tests \
  --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
```

Both approaches feed consistent metadata to the DTO, Form Request, Resource, and Test generators so every layer speaks the same language.

## Command options

| Option | Alias | Description |
| --- | --- | --- |
| `--api` | `-a` | Generate an API-oriented controller that targets the configured API namespace. |
| `--controller=Subdir` | `-c` | Place the controller inside a subfolder (forces controller generation). |
| `--requests` | `-r` | Generate `Store` and `Update` form requests. |
| `--tests` | `-t` | Force CRUD feature test generation. |
| `--no-controller` | `-nc` | Skip controller generation. |
| `--no-resource` | `-nr` | Skip API Resource generation. |
| `--no-dto` | `-nd` | Skip DTO generation. |
| `--no-test` | `-nt` | Skip feature tests. |
| `--no-provider` | `-np` | Skip provider creation and automatic registration. |
| `--from-migration=` | `-fm` | Provide a migration path or keyword to infer fields and relations. |
| `--fields=` | – | Inline schema definition (comma-separated) for modules without migrations. |
| `--force` | `-f` | Overwrite existing files instead of skipping them. |

Default behaviours (controller/resource/DTO/test/provider toggles) can be tuned in `config/module-generator.php` so recurring preferences are applied automatically.

## Schema-aware generation

The generator inspects your codebase to build an accurate picture of each module:

- **Migration parser** – `--from-migration` locates migrations matching the model name, extracts columns, relations, casts, defaults, and validation constraints, and shares them across the generated artefacts.
- **Inline schema DSL** – `--fields="title:string:nullable, user_id:foreign=users.id"` accepts multiple modifiers (nullable, unique, foreign keys) and produces the same metadata without a migration file.
- **Model fallbacks** – when migrations or schema hints are missing, the generators inspect your Eloquent model for fillable fields, casts, and relationships before falling back to sensible defaults.

## Generated components

Running `make:module` produces a cohesive stack tailored to your configuration:

- Repository interface and eloquent implementation with base class inheritance.
- Service interface and implementation with optional provider binding.
- DTO class hydrated from migration or schema metadata.
- Controller (API or web) that plugs DTOs, resources, and form requests together.
- API Resource that formats dates, booleans, and eager-loaded relations through the shared `StatusHelper`.
- Form Requests with `store`/`update` rule sets and localized validation messages (when enabled).
- Feature tests seeded with inferred fillable data, route stubs, and foreign key expectations.

## Feature test scaffolding

Enable `--tests` (or configure it as the default) to scaffold CRUD feature tests that:

- Assert success and failure paths for create, update, show, and delete operations.
- Leverage the inferred field metadata to generate payloads, validation cases, and relationship checks.
- Mount routes against a dedicated test URI segment so you can wire them to your preferred router quickly.

Tests honour your configured database connection—there is no forced SQLite driver, so they run against the environment you already maintain.

## Jalali date tooling

`ModuleGeneratorServiceProvider` binds a singleton-friendly `goli()` helper and Carbon macros so Jalali ↔ Gregorian conversions are available everywhere, including generated resources and helpers.

```php
use Efati\ModuleGenerator\Support\Goli;

$goli = goli(now())->format('Y/m/d');
$fromJalali = \Carbon\Carbon::fromJalali('1403/01/01 08:30:00', 'Asia/Tehran');
```

## Customising the output

- **Configuration** – adjust namespaces, paths, and default toggles in `config/module-generator.php` once, then regenerate modules with your preferred directory structure.
- **Stubs** – edit the published stubs in `resources/stubs/module-generator` to enforce house styles, add traits, tweak imports, or change response envelopes.
- **Providers** – if you disable provider generation, remember to bind repositories/services manually in your application container.

## Release highlights

### v6.2.4
- Inline schema parsing via `--fields` now understands nullability, unique constraints, and foreign keys, feeding the metadata to DTOs, resources, and tests.
- Migration introspection builds cast maps, fillable arrays, validation rules, and relation metadata shared across all generated classes.
- Provider generation auto-registers the binding in `bootstrap/providers.php` or `config/app.php`, removing manual steps after scaffolding.
- Resources format date and boolean fields through the bundled `StatusHelper`, and eager-loaded relations automatically resolve to companion resources.

Previous release notes are archived in [`CHABELOG.md`](CHABELOG.md) and [`docs/changelog.md`](docs/changelog.md).

## Resources

- [Full documentation](https://efati.github.io/laravel-module-generator/) – landing page, configuration guide, and advanced topics.
- [Usage reference](docs/usage.md) – option matrix, inline schema syntax, and command recipes.
- [Advanced features](docs/advanced.md) – deep dive into test scaffolding, Jalali tooling, and stub customisation.

## License

MIT

---

_پینوشت: با تشکر از gole davoodi 😆_
