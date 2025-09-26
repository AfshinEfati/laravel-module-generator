# Usage

```bash
php artisan make:module ModelName [options]
```

The `make:module` command scaffolds a complete module (model, repository, service, interfaces, DTO, controller, resource, form requests, provider, and tests) with one invocation.【F:src/Commands/MakeModuleCommand.php†L18-L174】

## Options

| Option | Alias | Description |
| --- | --- | --- |
| `--api` | `-a` | Generate an API-oriented controller targeting the configured namespace. |
| `--controller=Subdir` | `-c` | Place the controller inside a subdirectory (forces controller generation). |
| `--requests` | `-r` | Generate `Store` and `Update` form requests. |
| `--tests` | `-t` | Force CRUD feature test generation. |
| `--no-controller` | `-nc` | Skip controller generation. |
| `--no-resource` | `-nr` | Skip API Resource generation. |
| `--no-dto` | `-nd` | Skip DTO generation. |
| `--no-test` | `-nt` | Skip feature test generation. |
| `--no-provider` | `-np` | Skip provider creation and automatic registration. |
| `--from-migration=` | `-fm` | Point to a migration (path or keyword) to infer fields, casts, and relations. |
| `--fields=` | – | Provide inline schema metadata when migrations are unavailable. |
| `--force` | `-f` | Overwrite existing files instead of skipping them. |

Defaults are controlled via `config/module-generator.php`. Adjust the `defaults` section to match your common workflow and override per module with CLI flags.【F:src/config/module-generator.php†L37-L52】【F:src/Commands/MakeModuleCommand.php†L64-L132】

## Inline schema DSL (`--fields`)

Use the inline schema option to describe columns before a migration exists:

```bash
php artisan make:module Product --api --fields="name:string:unique, price:decimal(10,2), user_id:foreign=users.id"
```

- Separate fields with commas.
- Describe each field as `name:type[:modifier[:modifier...]]`.
- Supported modifiers include `nullable`, `unique`, and `foreign=table.column` (aliases `fk=` or `references=` work too).【F:src/Support/SchemaParser.php†L9-L138】

The parsed metadata feeds DTOs, resources, validation rules, and feature tests so the generated code aligns with your design from the start.【F:src/Commands/MakeModuleCommand.php†L92-L138】【F:src/Support/MigrationFieldParser.php†L214-L325】

## Migration introspection (`--from-migration`)

When migrations already exist, pass either a full path or a keyword:

```bash
php artisan make:module Booking --api --requests --from-migration=bookings
```

`MigrationFieldParser` scans the matching migration, extracts columns, relations, casts, defaults, and enum values, and shares the information with downstream generators. It also generates eager-load hints and validation rules for both store and update requests.【F:src/Support/MigrationFieldParser.php†L9-L213】【F:src/Support/MigrationFieldParser.php†L214-L325】

If the parser cannot find a migration or field metadata it falls back to inspecting the Eloquent model for fillable attributes and casts, logging helpful warnings in the console.【F:src/Commands/MakeModuleCommand.php†L101-L130】

## Controller modes

- `--api` produces a JSON-first controller that wraps responses with `StatusHelper`, supports DTO payloads, and returns API resources when enabled.【F:src/Generators/ControllerGenerator.php†L78-L167】【F:src/Stubs/Helpers/StatusHelper.php†L1-L83】
- Omit `--api` to generate a web controller scaffold that targets Blade views and standard redirect flows.【F:src/Generators/ControllerGenerator.php†L170-L248】

`--controller=Admin` (or any subpath) forces controller generation and nests the file within the configured namespace.【F:src/Commands/MakeModuleCommand.php†L73-L106】

## Feature tests

Enable `--tests` (or configure it as default) to generate CRUD feature tests that:

- Use inferred fillable metadata to build payloads and validation scenarios.
- Register predictable URIs (e.g., `/test-products`) so you can map them in routes quickly.
- Cover success and failure paths for store, update, show, index, and destroy actions.【F:src/Generators/TestGenerator.php†L11-L157】

Tests respect your project’s configured database connection—no SQLite swap is forced—so they integrate with existing pipelines seamlessly.【F:src/Generators/TestGenerator.php†L19-L44】

## Regenerating artefacts

When you update configuration or stubs, rerun the command with `--force` to overwrite existing files. The generator reports which files were created or skipped so you have immediate feedback.【F:src/Commands/MakeModuleCommand.php†L117-L174】
