# Swagger Generation

This package provides two ways to generate Swagger documentation for your API: model-based and route-based.

## Model-Based Generation

You can generate Swagger documentation for a specific module by using the `--swagger` option with the `make:module` command.

```bash
php artisan make:module Product --api --swagger
```

This command will generate a `ProductDoc.php` file in the `app/Docs` directory. This file will contain the OpenAPI annotations for the module's API.

You can also generate only the Swagger documentation for a module by using the `--swagger` option without any other options.

```bash
php artisan make:module Product --swagger
```

## Route-Based Generation

The package also provides a `make:swagger` command that can generate Swagger documentation by scanning your existing Laravel routes.

```bash
php artisan make:swagger
```

This command will scan all of your application's routes and generate a separate documentation file for each controller.

### Filtering Routes

You can filter the routes that are scanned by using the `--path` and `--controller` options.

The `--path` option allows you to filter the routes by a path prefix. For example, the following command will only scan the routes that start with `api/v1`:

```bash
php artisan make:swagger --path=api/v1
```

The `--controller` option allows you to filter the routes by a controller namespace. For example, the following command will only scan the routes that are handled by controllers in the `Api` namespace:

```bash
php artisan make:swagger --controller=Api
```

You can also combine these options to further filter the routes.

### Overwriting Files

By default, the `make:swagger` command will not overwrite existing documentation files. You can use the `--force` option to force the command to overwrite existing files.

```bash
php artisan make:swagger --force
```