# Installation

[🇮🇷 فارسی](/fa/installation/){ .language-switcher }

Get up and running with Laravel Module Generator by following the checklist below.

## Requirements

- PHP 8.1 or newer
- Laravel 10.x or 11.x (service providers are auto-discovered)
- Database connection configured for running generated feature tests

## 1. Require the package

```bash
composer require efati/laravel-module-generator
```

Composer registers `ModuleGeneratorServiceProvider`, which exposes the `make:module` command and helper classes out of the box.

## 2. Publish configuration and helpers

```bash
php artisan vendor:publish --tag=module-generator
```

This command copies:

- `config/module-generator.php` – adjust namespaces, paths, and default toggles here.
- Base repository/service classes plus the `StatusHelper` helper used by generated controllers and resources.

Keep the configuration file under version control so every environment shares the same structure.

## 3. (Optional) Publish stub templates

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

Stubs are exported to `resources/stubs/module-generator` and override the package defaults on every generation run. Update them to inject traits, logging, or naming conventions that suit your organisation.

## 4. Prepare the environment

- Ensure your `.env` database settings are correct. Generated feature tests run against your configured connection instead of forcing SQLite.
- Commit the published base classes if you plan to customise them—future module runs expect these files to exist.
- (Laravel 11) Keep `bootstrap/providers.php` tracked so provider auto-registration can be committed with each new module.

With the prerequisites complete you can jump to the [quickstart guide](quickstart.md) for command recipes and inline schema examples.
