---
title: مرجع API
description: مستندات کامل API برای Laravel Module Generator
---

# مرجع API

این صفحه API عمومی بسته Laravel Module Generator را مستند می‌کند.

## Command API

### MakeModuleCommand

دستور artisan اصلی برای تولید ماژول‌ها.

```bash
php artisan make:module ModuleName
```

**گزینه‌ها:**

- `--fields` - تعریف فیلدهای ماژول (فرمت CSV)
- `--force` - بازنویسی ماژول موجود
- `--only-routes` - تولید فقط routes

## Service API

### ModuleGenerator Service

```php
use AfshinEfati\LaravelModuleGenerator\Services\ModuleGenerator;

$generator = new ModuleGenerator();
$generator->generate($moduleName, $config);
```

**Methods:**

- `generate($name, $config)` - تولید ماژول جدید
- `publish()` - انتشار stubs بسته برای سفارشی‌سازی

## Facade

```php
use AfshinEfati\LaravelModuleGenerator\Facades\ModuleGenerator;

ModuleGenerator::generate('Users', ['fields' => ['id', 'name', 'email']]);
```

## پیکربندی

برای گزینه‌های پیکربندی دسترسی، `config/module-generator.php` را ببینید.
