# Generating Modules

The core of this package is the `make:module` Artisan command. It's a powerful tool that can generate a complete, test-ready module with a single command.

## Basic Usage

To generate a new module, you simply need to provide a name for it:

```bash
php artisan make:module Product
```

This will generate a `Product` module with the default components configured in `config/module-generator.php`.

## Generated Files

Each module generates the following structure:

```
App/
├── Models/
│   └── Product.php
├── Services/
│   ├── ProductService.php
│   └── ProductServiceInterface.php
├── Repositories/
│   ├── Contracts/ProductRepositoryInterface.php
│   └── Eloquent/ProductRepository.php
├── DTOs/
│   └── ProductDTO.php
├── Http/
│   ├── Controllers/Api/V1/ProductController.php
│   ├── Requests/Product/
│   │   ├── StoreProductRequest.php
│   │   └── UpdateProductRequest.php
│   └── Resources/ProductResource.php
├── Actions/Product/
│   ├── CreateAction.php
│   ├── UpdateAction.php
│   ├── DeleteAction.php
│   └── ... (more actions)
└── Providers/ProductServiceProvider.php

tests/
└── Feature/ProductCrudTest.php
```

## Command Options

| Option                | Alias | Description                                            |
| --------------------- | ----- | ------------------------------------------------------ |
| `--api`               | `-a`  | Generate API controller with form requests and actions |
| `--requests`          | `-r`  | Generate Store/Update form requests                    |
| `--tests`             | `-t`  | Generate feature test suite                            |
| `--no-controller`     | `-nc` | Skip controller generation                             |
| `--no-resource`       | `-nr` | Skip API Resource                                      |
| `--no-dto`            | `-nd` | Skip DTO                                               |
| `--no-test`           | `-nt` | Skip feature tests                                     |
| `--no-provider`       | `-np` | Skip service provider                                  |
| `--no-actions`        |       | Skip action layer                                      |
| `--controller=Subdir` | `-c`  | Place controller in subfolder                          |
| `--swagger`           | `-sg` | Generate OpenAPI annotations                           |
| `--no-swagger`        |       | Disable Swagger annotations                            |
| `--from-migration=`   | `-fm` | Infer schema from migration                            |
| `--fields=`           |       | Inline schema definition                               |
| `--force`             | `-f`  | Overwrite existing files                               |

## Common Examples

### API Module with Everything

```bash
php artisan make:module Product \
  --api --requests --tests --swagger \
  --fields="name:string, price:decimal(10,2), is_active:boolean"
```

### Web Module (Form-Based)

```bash
php artisan make:module BlogPost \
  --requests --tests \
  --fields="title:string, content:text, published_at:timestamp"
```

### Minimal Module (Services Only)

```bash
php artisan make:module Calculator \
  --no-controller --no-resource --no-test
```
