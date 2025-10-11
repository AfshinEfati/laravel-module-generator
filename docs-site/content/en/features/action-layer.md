# Action Layer

This package provides an option to generate an action layer for your modules. The action layer is a set of invokable classes that encapsulate the business logic of your application.

## Generating Actions

You can generate actions for a module by using the `--actions` flag with the `make:module` command.

```bash
php artisan make:module Product --actions
```

This command will generate a set of action classes in the `app/Actions/Product` directory. The generated actions will correspond to the CRUD operations of the module:

- `ListProductAction`
- `CreateProductAction`
- `ShowProductAction`
- `UpdateProductAction`
- `DeleteProductAction`

## Using Actions

The generated controller for the module will be automatically wired to use the new action classes. Here's an example of how the `store` method in the controller would look:

```php
public function store(StoreProductRequest $request, CreateProductAction $createProductAction)
{
    $dto = ProductDTO::fromRequest($request);
    $product = $createProductAction($dto);
    return ApiResponseHelper::successResponse(new ProductResource($product), 'created', 201);
}
```

As you can see, the controller is now much leaner, and the business logic is encapsulated in the `CreateProductAction` class.

## BaseAction

All generated actions extend a `BaseAction` class. This class provides a few helpful features, such as logging.

### Logging

The `BaseAction` class has a `logger` property that you can use to log messages. The logger is automatically configured to use the logging channel that is defined in the `config/module-generator.php` file.

Here's an example of how you can use the logger in an action:

```php
class CreateProductAction extends BaseAction
{
    // ...

    protected function handle(mixed ...$payload): mixed
    {
        $this->logger->info('Creating a new product...');
        return $this->service->store(...$payload);
    }
}
```