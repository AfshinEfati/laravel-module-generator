# CLI Reference

Quick reference for all available command options and generated file structure.

## Command Signature

```bash
php artisan make:module {name} [options]
```

## Required Arguments

| Argument | Description                                                          |
| -------- | -------------------------------------------------------------------- |
| `name`   | Module name in StudlyCase (e.g., `Product`, `BlogPost`, `OrderItem`) |

## Generation Options

| Option                | Short | Description                            |
| --------------------- | ----- | -------------------------------------- |
| `--api`               | `-a`  | Generate API controller (default: web) |
| `--requests`          | `-r`  | Generate Store/Update form requests    |
| `--tests`             | `-t`  | Generate CRUD feature tests            |
| `--actions`           |       | Generate action layer (7 actions)      |
| `--swagger`           | `-sg` | Generate OpenAPI documentation         |
| `--controller=Subdir` | `-c`  | Place controller in subfolder          |
| `--from-migration=`   | `-fm` | Infer schema from migration file       |
| `--fields=`           |       | Inline schema definition               |
| `--force`             | `-f`  | Overwrite existing files               |
| `--no-controller`     | `-nc` | Skip controller                        |
| `--no-resource`       | `-nr` | Skip API Resource                      |
| `--no-dto`            | `-nd` | Skip DTO                               |
| `--no-test`           | `-nt` | Skip tests                             |
| `--no-provider`       | `-np` | Skip service provider                  |
| `--no-actions`        |       | Skip actions                           |
| `--no-swagger`        |       | Disable Swagger                        |

## Generated File Structure

### Complete Module (`--api --tests --swagger`)

```
App/
├── Models/Product.php
├── Services/
│   ├── ProductService.php
│   └── ProductServiceInterface.php
├── Repositories/
│   ├── Contracts/ProductRepositoryInterface.php
│   └── Eloquent/ProductRepository.php
├── DTOs/ProductDTO.php
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
│   ├── ForceDeleteAction.php
│   ├── RestoreAction.php
│   ├── ListAction.php
│   └── ShowAction.php
├── Providers/ProductServiceProvider.php
└── Docs/ProductDoc.php

tests/
└── Feature/ProductCrudTest.php
```

## Field Definition Syntax

### Basic Types

```
--fields="name:string, age:integer, active:boolean"
```

### With Modifiers

```
--fields="name:string:unique, email:string:nullable"
```

### Advanced Types

```
--fields="
  id:id,
  name:string:unique,
  email:email:unique,
  age:integer:nullable,
  price:decimal(10,2),
  metadata:json:nullable,
  status:enum(pending,active,archived),
  user_id:foreignId:constrained(users),
  created_at:timestamp,
  updated_at:timestamp
"
```

## Available Modifiers

| Modifier               | Meaning                     |
| ---------------------- | --------------------------- |
| `unique`               | Unique constraint           |
| `nullable`             | Column allows NULL          |
| `foreign=table.column` | Foreign key                 |
| `constrained(table)`   | Foreign key with constraint |
| `unique(name)`         | Named unique index          |
| `index`                | Add index                   |
| `fulltext`             | Fulltext search             |
| `default(value)`       | Default value               |

## Repository API

### BaseRepository Methods

```php
// Find by primary key
$product = $repository->find($id);

// Dynamic queries
$products = $repository->getByDynamic(
    where: ['status' => 'active'],
    with: ['category'],
    limit: 20
);

// Find single record
$product = $repository->findDynamic(
    where: ['slug' => 'awesome-product'],
    with: ['category']
);
```

## Service API

### BaseService Methods

```php
// Standard CRUD
$product = $service->store($dto);
$product = $service->show($id);
$products = $service->index($filters);
$updated = $service->update($id, $dto);
$deleted = $service->destroy($id);

// Dynamic queries
$products = $service->getByDynamic(
    where: ['active' => true],
    with: ['reviews']
);
```

## Common Command Examples

### API Module - Complete

```bash
php artisan make:module Product --api --tests --swagger \
  --fields="name:string:unique, price:decimal(10,2), category_id:foreignId"
```

### Web Module - Simple

```bash
php artisan make:module BlogPost --tests \
  --fields="title:string, content:text, published_at:timestamp"
```

### From Existing Migration

```bash
php artisan make:module Product --api --from-migration
```

### Minimal (Services Only)

```bash
php artisan make:module Calculator \
  --no-controller --no-resource --no-test
```

## Environment Variables

```bash
MODULE_GENERATOR_FORCE_OVERWRITE=true   # Auto-overwrite files
MODULE_GENERATOR_DISABLE_TESTS=true     # Skip test generation
MODULE_GENERATOR_LOG_CHANNEL=module     # Custom logging channel
```

## Troubleshooting

**Issue:** Validation rules don't match schema

- **Solution:** Check field modifiers in `--fields` (e.g., `nullable`, `unique`)

**Issue:** Provider not auto-registered

- **Solution:** Verify `bootstrap/providers.php` (Laravel 11) or `config/app.php` (Laravel 10) is writable

**Issue:** Tests fail to execute

- **Solution:** Ensure `.env.testing` has valid database credentials

**Issue:** Controller not generated

- **Solution:** Use `--api` flag or `--controller=Subdir` to force generation
