```bash
php artisan make:module ModelName [options]
```

# Module Generator Usage

The `make:module` Artisan command scaffolds a fully structured Laravel module (model, repository, service, interfaces, DTO, controller, form requests, resources, and tests) in one step.

## Long Options

| Option                | Description |
|-----------------------|-------------|
| `--api`               | Generate an API-style controller under the configured API namespace (defaults to `Http/Controllers/Api/V1`). |
| `--controller=Subdir` | Place the controller inside the specified subdirectory and ensure a controller is generated. |
| `--requests`          | Generate `Store` and `Update` form request classes. |
| `--tests`             | Force CRUD Feature Test generation. |
| `--no-controller`     | Skip controller generation entirely. |
| `--no-resource`       | Skip API Resource generation. |
| `--no-dto`            | Skip DTO generation. |
| `--no-test`           | Skip Feature Test generation. |
| `--no-provider`       | Skip provider creation and auto-registration. |
| `--from-migration=`   | Provide a migration path or keyword to infer fields before a model exists. |
| `--force`             | Overwrite existing files instead of skipping them. |
| `--fields=`           | Supply an inline schema definition so generators can infer fillable fields, validation rules, and payloads. |

## Short Aliases

| Alias | Long option         |
|-------|---------------------|
| `-a`  | `--api`             |
| `-c`  | `--controller`      |
| `-r`  | `--requests`        |
| `-t`  | `--tests`           |
| `-nc` | `--no-controller`   |
| `-nr` | `--no-resource`     |
| `-nd` | `--no-dto`          |
| `-nt` | `--no-test`         |
| `-np` | `--no-provider`     |
| `-fm` | `--from-migration`  |
| `-f`  | `--force`           |

## Schema-aware generation

- Use `--fields="title:string|required, price:decimal:nullable"` to feed the generator inline metadata. Each field definition becomes part of the fillable array, validation rules, casts, and feature test payloads.
- Pair `--from-migration` with a file path or keyword (for example, `--from-migration=products`) to let the generator parse columns, nullable/unique flags, enum values, and foreign keys directly from your migration.
- When both options are provided, inline `--fields` metadata takes precedence; otherwise the generator falls back to migrations or an existing model to infer structure.

## Sample Scenarios

### API Module with Form Requests and Custom Controller Path

```bash
php artisan make:module Product --api --requests --controller=Admin
```

**Output highlights:**

- Generates the module skeleton plus a controller at `App/Http/Controllers/Api/V1/Admin/ProductController.php`.
- Adds `StoreProductRequest` and `UpdateProductRequest` classes with validation rules derived from your schema or `--fields` input.
- Produces API resources alongside the controller so your endpoints return structured JSON responses.

### Full CRUD Module with Tests

```bash
php artisan make:module Product --api --requests --controller=Admin --tests
```

**Output highlights:**

- Everything from the previous scenario.
- Adds `tests/Feature/ProductCrudTest.php` with happy-path and failure-path scenarios. See the [Feature Tests](advanced.md#test-generation) section for details on how these tests are structured.

### Slim Module for Internal Services

```bash
php artisan make:module Report --no-controller --no-resource --no-test
```

**Output highlights:**

- Generates core classes (model, repository, service, interfaces, DTO) without any HTTP layer assets.
- Suitable when the module is consumed internally via services or queued jobs without HTTP endpoints.

---

> Re-run the command with `--force` if you need to regenerate files after adjusting your stubs or configuration.
