---
title: API Reference
description: Complete API documentation for Laravel Module Generator
---

# API Reference

This page documents the public API of the Laravel Module Generator package.

## Command API

### MakeModuleCommand

The main artisan command for generating modules.

```bash
php artisan make:module ModuleName
```

**Options:**

- `--fields` - Define module fields (CSV format)
- `--force` - Overwrite existing module
- `--only-routes` - Generate only routes

## Service API

### ModuleGenerator Service

```php
use AfshinEfati\LaravelModuleGenerator\Services\ModuleGenerator;

$generator = new ModuleGenerator();
$generator->generate($moduleName, $config);
```

**Methods:**

- `generate($name, $config)` - Generate a new module
- `publish()` - Publish package stubs for customization

## Facade

```php
use AfshinEfati\LaravelModuleGenerator\Facades\ModuleGenerator;

ModuleGenerator::generate('Users', ['fields' => ['id', 'name', 'email']]);
```

## Configuration

See `config/module-generator.php` for available configuration options.
