# Changelog

[🇮🇷 فارسی](../fa/changelog.md){ .language-switcher }

A summary of notable releases for Laravel Module Generator. For the full history see [`CHABELOG.md`](https://github.com/AfshinEfati/laravel-module-generator/blob/main/CHABELOG.md) and [`version.md`](https://github.com/AfshinEfati/laravel-module-generator/blob/main/version.md).

## v6.2.4

- Improved `--fields` parsing to capture nullable/unique modifiers and inline foreign keys.
- Extracted table metadata (columns, relations, validation rules) directly from migrations for reuse across generators.
- Registered module service providers automatically in `bootstrap/providers.php` or `config/app.php`.
- Normalised resource responses via `StatusHelper`, including Jalali-friendly dates and boolean casting.

## v6.2.0

- Introduced the migration + inline schema parser to power DTOs, validation, and tests before models exist.
- Added `StatusHelper` and the `goli()` helper for consistent API responses.
- Expanded CRUD feature tests with full coverage for success and validation failure paths.

## v5.3

- Added CLI shortcuts (`-a`, `-c`, `-r`, `-t`, etc.) for frequently used flag combinations.
- Hardened file overwrites—existing files are skipped unless `--force` is supplied.
- Ensured controllers adapt automatically to API vs web flows depending on enabled artefacts.

## v5.2

- Generated end-to-end CRUD tests with detailed success and failure scenarios.
- Stopped forcing SQLite in tests; generators now honour the configured database connection.
- Improved DTO integration across controllers and services.

## v5.1

- Fixed form request generation edge cases and streamlined route configuration management.

## v5.0

- Major rewrite for Laravel 11 support with dynamic namespaces.
- Auto-wired service/repository bindings within generated service providers.
