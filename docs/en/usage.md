# Usage

[🇮🇷 فارسی](../fa/usage.md){ .language-switcher }


Explore common command combinations and workflows for everyday development. For a full walkthrough of the generated files, see the [Product module anatomy](module-anatomy.md).

## Command overview

```bash
php artisan make:module {Name}
  {--api}
  {--requests}
  {--actions}
  {--dto}
  {--resource}
  {--tests}
  {--policy}
  {--force}
  {--swagger}
  {--no-swagger}
  {--fields=}
  {--from-migration=}
```

Use `php artisan make:module --help` for the full flag list and descriptions.

### Optional Swagger annotations

Pass `--swagger` when generating API modules to scaffold OpenAPI (`@OA`) annotations for every CRUD endpoint. The generator automatically imports `OpenApi\Annotations` and skips annotation output when the swagger package is missing, emitting a warning instead. Install either `darkaonline/l5-swagger` or `zircote/swagger-php` before enabling the flag.

## Generating a REST API module

```bash
php artisan make:module Invoice \
  --api --actions --dto --resource --requests --tests \
  --fields="number:string:unique, issued_at:date, total:decimal(12,2)"
```

What you get:

- API controller with index/show/store/update/destroy actions.
- Action layer (`ListInvoiceAction`, `ShowInvoiceAction`, etc.) that encapsulates each use-case, complete with error logging when something goes wrong.
- Form requests that validate the schema supplied in `--fields`.
- DTO and resource classes that share the same field metadata.
- Feature tests that cover happy paths and validation failures.
- Form requests are generated inside `App\Http\Requests\Invoice\` so large apps stay organised automatically.

```php
// app/Actions/Invoice/ShowInvoiceAction.php
class ShowInvoiceAction extends BaseAction
{
    public function __construct(private InvoiceService $service) {}

    protected function handle(mixed $modelOrId): ?Invoice
    {
        $id = $modelOrId instanceof Invoice ? $modelOrId->getKey() : $modelOrId;

        return $this->service->show($id);
    }
}

// app/Http/Controllers/Api/V1/InvoiceController.php (generated)
public function show(Invoice $invoice): mixed
{
    $model = ($this->showAction)($invoice->getKey());
    if (!$model) {
        return ApiResponseHelper::errorResponse('not found', 404);
    }

    $model->load(['customer', 'lines']);

    return ApiResponseHelper::successResponse(new InvoiceResource($model), 'success');
}
```

> Actions are invokable (`__invoke`) so you can reuse them from jobs, event listeners, or console commands without going through the controller. The base action logs the full exception context whenever an error bubbles up.

Need to stick with the traditional service-centric controller? Pass `--no-actions` (or set `with_actions` to `false` in the config) and the generator will wire controllers directly to the service again.

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

Use the [advanced guides](advanced.md) when you need to hook into lifecycle events, override base classes, or build custom generators.
