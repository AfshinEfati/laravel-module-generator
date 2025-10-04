# CLI & file reference

[🇮🇷 فارسی](../fa/reference.md){ .language-switcher }


Use this reference when you need a concise view of the available options and generated files.

## Command options

| Option | Description |
| --- | --- |
| `--api` | Generates an API controller with RESTful routes. |
| `--requests` | Creates form requests for store/update actions with validation rules derived from the schema. |
| `--actions` | Scaffolds use-case classes (List/Show/Create/Update/Delete) and rewires controllers to invoke them. |
| `--dto` | Produces a Data Transfer Object that mirrors the fields passed to the command. |
| `--resource` | Generates a Laravel API resource for consistent response formatting. |
| `--tests` | Builds a feature test suite covering CRUD scenarios. |
| `--policy` | Scaffolds a policy class and registers it with the module provider. |
| `--fields=` | Accepts inline schema definitions (`name:type:modifier`). |
| `--from-migration=` | Points to a migration file to reuse its column metadata. |
| `--force` | Overwrites existing files without prompting. |

Combine flags as needed to match your module requirements. Inline schema and migration parsing can be used together—fields found in the migration are merged with inline overrides.

## Generated structure

A typical module created with `--api --requests --dto --resource --tests` results in the following directories:

```
app/Modules/{Module}/
├── Contracts/
├── DTOs/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Providers/
├── Repositories/
├── Resources/
└── Services/
```

The generator also registers the service provider and, when tests are enabled, creates feature tests under `tests/Feature/Modules/{Module}`.

## Base repository & service API

Both base classes are published into your application the first time Artisan boots. They expose a consistent surface area regardless of the module you generate.

| Class | Method | Description |
| --- | --- | --- |
| `BaseRepository` | `find(int|string $id)` | Looks up a model by primary key. |
|  | `findDynamic(array ...$clauses)` | Builds a fluent query based on the arrays you pass (supports `where`, `orWhere`, `whereBetween`, `whereNull`, `whereRaw`, …) and returns the first match. |
|  | `getByDynamic(array ...$clauses)` | Same signature as `findDynamic`, but returns an `Illuminate\Support\Collection` of all matching records. |
|  | `buildDynamicQuery()` | Protected helper that you can reuse inside custom repository methods if you want to add additional clauses. |
| `BaseService` | `index()` / `show()` / `store()` / `update()` / `destroy()` | Wrapper methods that proxy to the repository while normalising DTO payloads. |
|  | `findDynamic()` / `getByDynamic()` | Delegate to the repository so higher layers never need to touch Eloquent directly. |

If you publish and customise the base classes, the generator will automatically extend or implement your versions.

## Schema syntax cheatsheet

| Example | Meaning |
| --- | --- |
| `name:string:unique` | Required string column with unique constraint. |
| `price:decimal(10,2):nullable` | Nullable decimal column with precision and scale. |
| `user_id:foreignId:constrained(users)` | Foreign key column referencing the `users` table. |
| `metadata:json:nullable` | Optional JSON column. |

## Troubleshooting

- **Validation rules look incorrect** – double-check the field definitions. Modifiers like `nullable` and `unique` map directly to validation rules.
- **Providers are not registered** – confirm that `bootstrap/providers.php` (Laravel 11) or `config/app.php` (Laravel 10) is tracked in git and writable.
- **Tests fail to run** – ensure the `.env` database credentials point to a test database with migration history.

When in doubt, re-run the generator with `--verbose` to inspect detailed output.
