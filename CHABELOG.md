# Changelog

All notable changes to this package are documented here. The current release line is **v6.2.x**.

## [6.2.4] - 2025-06-04
### ✨ Added
- Richer inline schema parsing via `--fields`, including nullable, unique, and foreign key modifiers that flow into DTOs, resources, and tests.
- Extended migration introspection to capture relation metadata, enum values, and validation rules for downstream generators.

### 🔧 Changed
- Provider generation now auto-registers bindings in `bootstrap/providers.php` or `config/app.php`, removing manual steps after scaffolding.
- API Resources call `StatusHelper` to normalise date/boolean fields and resolve eager-loaded relations to dedicated resources.

### 🛠 Fixed
- Harmonised generator output when using inline schema data versus migration-derived metadata so validation, DTOs, and tests stay in sync.

## [6.2.0] - 2025-05-12
### ✨ Added
- Migration parsing engine and inline schema DSL that share metadata across DTOs, form requests, resources, and feature tests.
- Bundled Jalali tooling (`goli()` helper and Carbon macros) plus `StatusHelper` response utilities for generated controllers and resources.
- Feature test generator that seeds CRUD scenarios with inferred metadata and honours the project’s configured database connection.

### 🔧 Changed
- Controller generator adapts to API vs. web modes while honouring DTO/resource toggles and relation eager-loading hints.
- Provider generator wires repositories and services together with optional auto-registration.

## [5.3] - 2024-11-30
### ✨ Added
- Short CLI aliases (`-a`, `-c`, `-r`, `-t`, etc.) for faster module generation.

### 🔧 Changed
- Safe overwrite behaviour: existing files are skipped unless `--force` is supplied, with feedback on skipped paths.

## [5.2] - 2024-09-15
### ✨ Added
- Full CRUD feature tests with success and failure scenarios that leverage the same metadata as DTOs and requests.

### 🔧 Changed
- Tests honour the configured database connection instead of forcing SQLite, aligning with `.env` settings.
- Improved DTO integration in controllers and services.

## [5.1] - 2024-07-02
### 🛠 Fixed
- Form request generation and configuration path handling improvements.

## [5.0] - 2024-05-10
### ✨ Added
- Major refactor for Laravel 11 support with dynamic namespace handling.
- Service & Repository auto-binding via generated providers.

Legacy history prior to 5.x supported basic repository/service/DTO scaffolding.
