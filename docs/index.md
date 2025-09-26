# Laravel Module Generator

Welcome to the documentation hub for the **Laravel Module Generator** package. The guides in this site explain how to scaffold opinionated Laravel modules, customise the generated code, and keep the workflow aligned with your team’s conventions.

## Highlights in v6.2.4

- Schema-aware generation that combines migration introspection and inline `--fields` definitions to hydrate DTOs, validation rules, API resources, and feature tests with a single source of truth.
- Automatic provider registration that binds repositories and services as soon as they are generated, keeping your container in sync.
- Bundled `StatusHelper` and Jalali-aware `Goli` toolkit for consistent API responses and Carbon interoperability out of the box.
- Optional CRUD feature tests that reuse the same metadata to cover happy-path and failure-path scenarios.

## Quick start

```bash
composer require efati/laravel-module-generator
php artisan vendor:publish --tag=module-generator
php artisan make:module Product --api --requests --tests --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
```

Use `--fields="name:string:unique, price:decimal(10,2)"` when a migration does not exist yet—the inline schema DSL unlocks the same rich metadata for DTOs, resources, and tests.

## Next steps

- [Installation](installation.md) – requirements, publishing assets, and environment preparation.
- [Configuration](configuration.md) – customise namespaces, default toggles, and stub overrides.
- [Usage](usage.md) – option reference, inline schema recipes, and command examples.
- [Advanced features](advanced.md) – test scaffolding, Jalali tooling, and extending the generator.
- [Changelog](changelog.md) – release notes for the 6.x series.

Need to ship the docs? See the [GitHub Pages guide](github-pages-setup.md) for details on the automated workflow.
