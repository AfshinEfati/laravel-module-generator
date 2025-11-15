# Action Layer

The action layer provides invokable classes that encapsulate business logic and keep controllers clean and focused on HTTP concerns.

## Generating Actions

Use the `--api` flag to generate actions automatically (included in comprehensive module generation):

```bash
php artisan make:module Product --api
```

This generates 7 action classes in `App/Actions/Product/`:

- `CreateAction.php` – Handle creation logic
- `UpdateAction.php` – Handle update logic
- `DeleteAction.php` – Handle deletion logic
- `ForceDeleteAction.php` – Hard delete
- `RestoreAction.php` – Restore soft-deleted
- `ListAction.php` – Fetch collections
- `ShowAction.php` – Fetch single record

## Using Actions

Generated controller automatically uses actions:

```php
public function store(StoreProductRequest $request, CreateAction $action)
{
    $dto = ProductDTO::fromRequest($request);
    $product = $action($dto);
    return new ProductResource($product);
}

public function update(UpdateProductRequest $request, Product $product, UpdateAction $action)
{
    $updated = $action($product, $request->validated());
    return new ProductResource($updated);
}

public function destroy(Product $product, DeleteAction $action)
{
    $action($product);
    return response()->noContent();
}
```

## Action Benefits

- **Testability** – Actions are easily testable in isolation
- **Reusability** – Use same action in console commands, jobs, webhooks
- **Clean Controllers** – Controllers become HTTP marshals only
- **Logging** – All actions automatically log via configured channel
- **Exception Handling** – Consistent error handling across actions

## Custom Actions

Override default actions by editing generated files:

```php
class CreateAction extends BaseAction
{
    public function __invoke(ProductDTO $dto): Product
    {
        Log::info("Creating product: {$dto->name}");

        $product = Product::create($dto->toArray());

        Event::dispatch(new ProductCreated($product));

        return $product;
    }
}
```

## BaseAction Features

All actions extend `BaseAction` with:

- `$this->logger` – Pre-configured logging
- `$this->service` – Injected service dependency
- `$this->repository` – Injected repository dependency
- Exception handling and response formatting
