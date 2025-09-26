# Advanced Features

## Feature test internals

The feature test generator builds a metadata map that drives every assertion. It exports inferred fillable fields, casts, foreign keys, and enum values into the test class so you can expand coverage quickly.【F:src/Generators/TestGenerator.php†L11-L157】

Key behaviours:

- Generates a predictable base URI (e.g., `/test-products`) and route name prefix for quick wiring.【F:src/Generators/TestGenerator.php†L28-L41】
- Hydrates field metadata from migrations, inline schema definitions, or model `fillable` and `casts` properties.【F:src/Generators/TestGenerator.php†L43-L107】
- Serialises nested metadata into arrays you can reuse in further assertions, reducing setup duplication.【F:src/Generators/TestGenerator.php†L83-L157】

## Migration metadata reuse

`MigrationFieldParser` is shared by DTO, resource, form request, and test generators. It analyses migration statements, including enums, decimal precision, and chained modifiers, to produce a canonical map of your table schema.【F:src/Support/MigrationFieldParser.php†L9-L213】

The parser also resolves relationships and eager-load hints so generated resources include nested resources when available.【F:src/Support/MigrationFieldParser.php†L214-L325】【F:src/Generators/ResourceGenerator.php†L53-L128】

## Status helper

Publishing the assets installs `App\Helpers\StatusHelper`, which centralises API response envelopes and normalises date/boolean fields. Resources call it automatically, but you can extend it to map domain-specific enumerations or add localisation hooks.【F:src/Stubs/Helpers/StatusHelper.php†L1-L83】【F:src/Generators/ResourceGenerator.php†L77-L158】

## Jalali tooling

`ModuleGeneratorServiceProvider` binds the `goli()` helper and registers Carbon macros, giving you Jalali ↔ Gregorian conversions throughout your app and generated resources.【F:src/ModuleGeneratorServiceProvider.php†L14-L53】 The `Goli` class provides parsing, formatting, digit localisation, and diff helpers that integrate with Carbon seamlessly.【F:src/Support/Goli.php†L1-L120】

```php
use Efati\ModuleGenerator\Support\Goli;

goli(now())->format('Y/m/d');
\Carbon\Carbon::fromJalali('1403/01/01 08:30:00');
```

## Customising stubs

Published stubs live in `resources/stubs/module-generator` and are resolved before the package defaults. Update them to:

- Inject traits (e.g., auditing, soft deletes) directly into generated models, repositories, or services.
- Change controller response envelopes, logging, or exception handling to match your API style.
- Add domain-specific interfaces or constructor signatures to DTOs and services.

After editing stubs, rerun `make:module` with `--force` for existing modules to pick up the changes.【F:src/Commands/MakeModuleCommand.php†L117-L174】
