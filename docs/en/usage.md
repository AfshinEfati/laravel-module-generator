# Usage

[🇮🇷 فارسی](/fa/usage/){ .language-switcher }

Explore common command combinations and workflows for everyday development.

## Command overview

```bash
php artisan make:module {Name}
  {--api}
  {--requests}
  {--dto}
  {--resource}
  {--tests}
  {--policy}
  {--force}
  {--fields=}
  {--from-migration=}
```

Use `php artisan make:module --help` for the full flag list and descriptions.

## Generating a REST API module

```bash
php artisan make:module Invoice \
  --api --dto --resource --requests --tests \
  --fields="number:string:unique, issued_at:date, total:decimal(12,2)"
```

What you get:

- API controller with index/show/store/update/destroy actions.
- Form requests that validate the schema supplied in `--fields`.
- DTO and resource classes that share the same field metadata.
- Feature tests that cover happy paths and validation failures.

## Using migrations as the source of truth

```bash
php artisan make:module Invoice \
  --api --requests --tests \
  --from-migration=database/migrations/2024_05_01_000001_create_invoices_table.php
```

When you point to a migration the generator parses column types, defaults, nullable fields, foreign keys, and comments to build DTOs, resources, and validation rules automatically.

## DTO-only support modules

If you only need the data structures, skip the API flag and disable resources/tests.

```bash
php artisan make:module Money --dto --fields="amount:decimal(8,2), currency:string:3"
```

This produces DTO classes plus supporting factories so you can reuse the structure in other services.

## Regenerating after edits

Stubs can be customised, so it is safe to re-run the command with `--force` when you add new fields. The generator respects existing files and only overwrites what is necessary.

```bash
php artisan make:module Invoice --fields="number:string, total:decimal(12,2), due_at:date" --force
```

If you manually edited generated files, rely on your version control system to review the diff before committing.

## Testing

The generated feature tests target the database connection defined in `.env`. Run them locally to confirm everything passes before pushing.

```bash
phpunit --testsuite=Feature
```

Use the [advanced guides](/en/advanced/) when you need to hook into lifecycle events, override base classes, or build custom generators.
