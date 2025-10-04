# Changelog

[🇮🇷 فارسی](../fa/changelog.md){ .language-switcher }

A summary of notable releases for Laravel Module Generator. For the full history see [`CHABELOG.md`](https://github.com/AfshinEfati/laravel-module-generator/blob/main/CHABELOG.md) and [`version.md`](https://github.com/AfshinEfati/laravel-module-generator/blob/main/version.md).


## v7.1.0

- Added `findDynamic()` and `getByDynamic()` to the base repository/service classes and propagated the API to generated contracts so modules can express complex filters without hand-written queries.【F:src/Stubs/BaseRepository.php†L23-L160】【F:src/Stubs/Module/Service/concrete.stub†L23-L120】
- Renamed Jalali helper methods to the clearer `parseGoli`, `toGoliDateString`, etc., and aliased Carbon as `CarbonDate` to prevent namespace clashes.【F:src/Support/Goli.php†L18-L720】
- Expanded the documentation with a Product module walkthrough and a dedicated Goli cookbook so teams can onboard quickly. See the new “Module anatomy” and “Goli date helper guide” sections in the docs.

## v7.0.0

- Generator output now honours the published base repository/service classes and interfaces, so any edits you make to the shared layer are reused automatically.【F:src/Support/BaseClassLocator.php†L9-L180】【F:src/Generators/ServiceGenerator.php†L9-L72】【F:src/Generators/RepositoryGenerator.php†L9-L76】
- Publishable assets (config, helper, base classes, stubs) are synchronised automatically whenever Artisan boots, removing the initial `vendor:publish` requirement.【F:src/ModuleGeneratorServiceProvider.php†L31-L68】
- Migration analysis captures fillable fields and `belongsTo` relations while ignoring index-only definitions, producing richer DTOs, resources, and tests.【F:src/Support/MigrationFieldParser.php†L9-L325】

## v6.2.4

- Improved `--fields` parsing to capture nullable/unique modifiers and inline foreign keys.
- Extracted table metadata (columns, relations, validation rules) directly from migrations for reuse across generators.
- Registered module service providers automatically in `bootstrap/providers.php` or `config/app.php`.
- Normalised resource responses via `ApiResponseHelper`, including Jalali-friendly dates and boolean casting.

## v6.2.0

- Introduced the migration + inline schema parser to power DTOs, validation, and tests before models exist.
- Added `ApiResponseHelper` and the `goli()` helper for consistent API responses.
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
