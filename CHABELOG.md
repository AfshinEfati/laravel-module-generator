# Changelog

All notable changes to this package are documented here. The current release line is **v7.x**.

## [Unreleased]
### ✨ Added
- **NEW Command: `make:swagger`** - Route-based Swagger documentation generator that scans existing Laravel routes and generates OpenAPI documentation independently of models. Supports filtering by path prefix and controller namespace.【F:src/Commands/GenerateSwaggerCommand.php†L1-L450】
- Swagger-only generation mode: using `--swagger` flag alone now generates only the Swagger documentation file without creating other module files (Repository, Service, DTO, Controller, etc.).【F:src/Commands/MakeModuleCommand.php†L102-L213】

### 🔧 Changed
- Swagger documentation now includes proper JSON response content types with `@OA\JsonContent()` annotations for all endpoints.【F:src/Generators/SwaggerDocGenerator.php†L103-L122】
- All Swagger endpoints now document 401 (Unauthenticated) responses with proper JSON error format when authentication is required.【F:src/Generators/ControllerGenerator.php†L511-L582】【F:src/Commands/MakeModuleCommand.php†L492-L563】
- Enhanced error responses (404, 422) in Swagger documentation with structured JSON examples including message properties.【F:src/Generators/SwaggerDocGenerator.php†L110-L121】

### 📚 Documentation
- Added comprehensive route-based Swagger documentation guide with examples and troubleshooting.【F:ROUTE_BASED_SWAGGER.md†L1-L500】
- Added comprehensive Swagger/OpenAPI documentation section in README with usage examples and Laravel configuration tips for proper JSON responses.【F:README.md†L132-L200】
- Updated command options table to clarify swagger-only behavior.【F:README.md†L94】

## [7.1.6] - 2025-09-14
### ✨ Added
- Optional `--swagger` flag now generates standalone `App\Docs\{Module}Doc` files with OpenAPI annotations (tags, CRUD endpoints, request/response skeletons), automatically marking endpoints as secured whenever configured middleware (e.g. `auth:sanctum`) is applied and warning if `swagger-php`/`l5-swagger` is missing.【F:src/Commands/MakeModuleCommand.php†L37-L115】【F:src/Generators/ControllerGenerator.php†L20-L520】【F:src/Generators/SwaggerDocGenerator.php†L9-L187】【F:src/Stubs/Module/Doc/swagger.stub†L1-L12】
- Documentation site migrated to Nuxt 3 + Tailwind with bilingual navigation and GitHub Pages build (`npm run generate`), and all Markdown content now lives under `docs-site/content/*`.【F:docs-site/nuxt.config.ts†L1-L34】【F:docs-site/pages/[lang]/[...slug].vue†L1-L18】【F:.github/workflows/docs.yml†L1-L45】

### 🔧 Changed
- Runtime model inspection now falls back to migration parsing for every module so DTOs, resources, requests, and tests always receive full field metadata; resources import the model, annotate with `@mixin ModelName`, and always expose the primary `id`.【F:src/Commands/MakeModuleCommand.php†L99-L170】【F:src/Generators/ResourceGenerator.php†L34-L207】【F:src/Support/ModelInspector.php†L23-L78】
- Swagger annotations are emitted into dedicated doc classes instead of controllers, keeping generated controllers lightweight while still documenting every endpoint; the generator also disables `--swagger` automatically unless `--api` is present.【F:src/Generators/ControllerGenerator.php†L20-L512】【F:src/Generators/SwaggerDocGenerator.php†L9-L120】【F:src/Commands/MakeModuleCommand.php†L100-L115】【F:src/Stubs/Module/Controller/api.stub†L1-L45】
- Form requests are generated inside `Http/Requests/{Module}` namespaces/directories, keeping large projects organised without manual moves.【F:src/Generators/FormRequestGenerator.php†L19-L205】
- Controller generators reference the nested request namespace so imports remain valid when requests are grouped per module.【F:src/Generators/ControllerGenerator.php†L29-L105】

### 🛠 Fixed
- Resource mixins previously rendered doubled namespace slashes and omitted the `id` field when models lacked `$fillable`; swagger annotations automatically disable themselves when the library is absent, preventing fatal errors during generation.【F:src/Generators/ResourceGenerator.php†L34-L207】【F:src/Commands/MakeModuleCommand.php†L102-L112】

## [7.1.1] - 2025-08-26
### 🔧 Changed
- Generated controllers that rely on actions now reuse the bound model instance (`->getKey()`), preventing redundant queries before delegating to the action layer.
- `BaseAction` logs the full exception object for richer diagnostics.
- Documentation expanded with actionable examples for the action flag and `--no-actions` toggle.

## [7.1.0] - 2025-08-20
### ✨ Added
- Dynamic repository/service helpers (`findDynamic`, `getByDynamic`) with stub updates so every generated module can issue complex filters out of the box.【F:src/Stubs/BaseRepository.php†L23-L160】【F:src/Stubs/Module/Repository/contract.stub†L12-L38】
- Detailed documentation for the Product sample module and a Goli cookbook covering parsing, formatting, and helper integration.

### 🔧 Changed
- Renamed Jalali-facing APIs to the clearer `parseGoli`, `toGoliDateString`, etc., and alias Carbon imports to `CarbonDate` to avoid collisions.【F:src/Support/Goli.php†L18-L720】

## [7.0.0] - 2025-07-05
### ✨ Added
- Base class discovery ensures generated repositories and services extend/implement the versions you have published into your application, so local modifications propagate automatically.【F:src/Support/BaseClassLocator.php†L9-L180】【F:src/Generators/ServiceGenerator.php†L9-L72】【F:src/Generators/RepositoryGenerator.php†L9-L76】
- Automatic asset mirroring copies config, helper, base classes, and stub resources into the host app whenever Artisan runs, eliminating the initial `vendor:publish` prompt.【F:src/ModuleGeneratorServiceProvider.php†L31-L68】
- Migration parsing now captures concrete fillable fields alongside `belongsTo` relations while skipping index-only statements, feeding richer metadata to DTOs, resources, and tests.【F:src/Support/MigrationFieldParser.php†L9-L325】

## [6.2.4] - 2025-06-04
### ✨ Added
- Richer inline schema parsing via `--fields`, including nullable, unique, and foreign key modifiers that flow into DTOs, resources, and tests.【F:src/Support/SchemaParser.php†L9-L138】【F:src/Commands/MakeModuleCommand.php†L92-L138】
- Extended migration introspection to capture relation metadata, enum values, and validation rules for downstream generators.【F:src/Support/MigrationFieldParser.php†L9-L213】【F:src/Support/MigrationFieldParser.php†L214-L325】

### 🔧 Changed
- Provider generation now auto-registers bindings in `bootstrap/providers.php` or `config/app.php`, removing manual steps after scaffolding.【F:src/Generators/ProviderGenerator.php†L37-L72】
- API Resources call `ApiResponseHelper` to normalise date/boolean fields and resolve eager-loaded relations to dedicated resources.【F:src/Generators/ResourceGenerator.php†L77-L158】【F:src/Stubs/Helpers/ApiResponseHelper.php†L1-L83】

### 🛠 Fixed
- Harmonised generator output when using inline schema data versus migration-derived metadata so validation, DTOs, and tests stay in sync.【F:src/Commands/MakeModuleCommand.php†L92-L151】

## [6.2.0] - 2025-05-12
### ✨ Added
- Migration parsing engine and inline schema DSL that share metadata across DTOs, form requests, resources, and feature tests.【F:src/Commands/MakeModuleCommand.php†L47-L138】【F:src/Support/MigrationFieldParser.php†L9-L213】【F:src/Support/SchemaParser.php†L9-L138】
- Bundled Jalali tooling (`goli()` helper and Carbon macros) plus `ApiResponseHelper` response utilities for generated controllers and resources.【F:src/ModuleGeneratorServiceProvider.php†L14-L53】【F:src/Stubs/Helpers/ApiResponseHelper.php†L1-L83】
- Feature test generator that seeds CRUD scenarios with inferred metadata and honours the project’s configured database connection.【F:src/Generators/TestGenerator.php†L11-L157】【F:src/Generators/TestGenerator.php†L19-L44】

### 🔧 Changed
- Controller generator adapts to API vs. web modes while honouring DTO/resource toggles and relation eager-loading hints.【F:src/Generators/ControllerGenerator.php†L1-L167】【F:src/Generators/ControllerGenerator.php†L170-L248】
- Provider generator wires repositories and services together with optional auto-registration.【F:src/Generators/ProviderGenerator.php†L9-L72】

## [5.3] - 2024-11-30
### ✨ Added
- Short CLI aliases (`-a`, `-c`, `-r`, `-t`, etc.) for faster module generation.【F:src/Commands/MakeModuleCommand.php†L18-L174】

### 🔧 Changed
- Safe overwrite behaviour: existing files are skipped unless `--force` is supplied, with feedback on skipped paths.【F:src/Commands/MakeModuleCommand.php†L117-L174】

## [5.2] - 2024-09-15
### ✨ Added
- Full CRUD feature tests with success and failure scenarios that leverage the same metadata as DTOs and requests.【F:src/Generators/TestGenerator.php†L11-L157】

### 🔧 Changed
- Tests honour the configured database connection instead of forcing SQLite, aligning with `.env` settings.【F:src/Generators/TestGenerator.php†L19-L44】
- Improved DTO integration in controllers and services.【F:src/Generators/ControllerGenerator.php†L78-L167】【F:src/Commands/MakeModuleCommand.php†L113-L150】

## [5.1] - 2024-07-02
### 🛠 Fixed
- Form request generation and configuration path handling improvements.【F:src/Commands/MakeModuleCommand.php†L100-L151】【F:src/config/module-generator.php†L19-L53】

## [5.0] - 2024-05-10
### ✨ Added
- Major refactor for Laravel 11 support with dynamic namespace handling.【F:src/ModuleGeneratorServiceProvider.php†L14-L53】【F:src/config/module-generator.php†L5-L53】
- Service & Repository auto-binding via generated providers.【F:src/Generators/ProviderGenerator.php†L9-L72】

Legacy history prior to 5.x supported basic repository/service/DTO scaffolding.
