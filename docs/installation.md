# Installation

Get up and running with the Laravel Module Generator by following the checklist below.

## Requirements

- PHP 8.1 or newer
- Laravel 10.x or 11.x (the package relies on auto-discovered service providers)
- Access to your project’s database connection for running generated feature tests

## 1. Require the package

```bash
composer require efati/laravel-module-generator
```

Composer adds the package and registers `ModuleGeneratorServiceProvider`, which exposes the `make:module` command and the `goli()` helper automatically.

## 2. Publish configuration and base classes

```bash
php artisan vendor:publish --tag=module-generator
```

This step copies:

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
- (Laravel 11) keep `bootstrap/providers.php` tracked so provider auto-registration can be committed with each new module.

With the prerequisites complete you can jump to the [usage guide](usage.md) for command recipes and inline schema examples.
