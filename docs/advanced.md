# Advanced Features

## Test Generation

The generator ships with an opinionated feature test scaffold that mirrors end-to-end CRUD flows. Activate it with the [`--tests` option](usage.md) whenever you run `php artisan make:module`, or rely on the short `-t` alias for quick toggling.

> **Tip:** The generated scenarios run against the database connection already configured in your `.env`, so you can reuse your existing testing stack without pulling in extra packages or drivers.

Each suite exercises the module through a mix of happy-path and failure-path checks:

- Creates records with valid payloads to ensure repository and controller wiring works from request to persistence.
- Sends malformed data to surface validation errors triggered by the generated Form Requests.
- Attempts to fetch, update, or delete non-existent IDs to confirm `404 Not Found` handling.
- Confirms successful update and delete operations, including JSON structure assertions when resources are enabled.

```php
// Example: generated test skeleton
public function test_user_can_create_product()
{
    $payload = Product::factory()->make()->toArray();

    $this->postJson(route('products.store'), $payload)
        ->assertCreated()
        ->assertJsonFragment(['name' => $payload['name']]);
}
```

For a deeper walkthrough of all command switches and short aliases, see the [Usage guide](usage.md).

## Goli Date Helper

`Goli` is the built-in Jalali date toolkit exposed via the `goli()` helper and a dedicated service container binding. It gives you Carbon interoperability, digit localisation, and Gregorian ⇆ Jalali conversions out of the box.

> **Note:** There is no need to pull in third-party libraries like Verta—the helper is bundled with the package. Review the [Installation instructions](installation.md) if you skipped the service provider registration step.

Common entry points include parsing, formatting, and bridging to Carbon macros:

```php
use Efati\ModuleGenerator\Support\Goli;

// via the global helper
$formatted = goli(now())->format('Y/m/d');

// resolve via the service container
$goli = app(Goli::class, ['datetime' => '2024-03-20 12:00:00']);
$jalali = $goli->toJalaliDateString();

// Carbon macros registered by the service provider
$diff = now()->toJalali()->diffForHumans();
$fromJalali = \Carbon\Carbon::fromJalali('1403/01/01 08:30:00', 'Asia/Tehran');
```

The helper accepts Jalali strings (with optional Persian digits), converts between calendars, and keeps all Carbon chaining capabilities intact. When you need raw arrays for storage or API responses, reach for the conversion helpers like `toArray()` or `toGregorian()`.

## Update-safe validation

When you generate Form Requests, update rules automatically convert any `unique:table,column` strings into `Rule::unique()->ignore()` calls using the route parameter (model instance or scalar ID). This keeps validation accurate when editing existing records and avoids duplicate key errors during PATCH/PUT requests—even when your routes leverage implicit binding.
