# Laravel Module Generator

A modular generator package for Laravel, designed to quickly scaffold models, repositories, services, DTOs, controllers, requests, and resources using simple CLI commands.

---

## 📦 Installation

```
composer require efati/laravel-module-generator --dev
```

Then publish required base classes:

```
php artisan vendor:publish --tag=module-generator
```

This will publish:
- `BaseRepository`, `BaseRepositoryInterface`
- `BaseService`, `BaseServiceInterface`
- `App\Helpers\StatusHelper`

---

## 🚀 Usage

```
php artisan make:module ModelName [--requests] [--controller=Subfolder] [--api] [--force]
```

---

## 🧱 Examples

### Create only base components (Model, Repository, Service, DTO):

```
php artisan make:module Product
```

This creates:
- `app/Models/Product.php`
- `app/Repositories/ProductRepository.php`
- `app/Repositories/Interfaces/ProductRepositoryInterface.php`
- `app/Services/ProductService.php`
- `app/Services/Interfaces/ProductServiceInterface.php`
- `app/DTO/ProductDTO.php`

---

### Create with Form Requests:

```
php artisan make:module Product --requests
```

This adds:
- `app/Http/Requests/Product/StoreProductRequest.php`
- `app/Http/Requests/Product/UpdateProductRequest.php`

With rule auto-generation based on model fields.

---

### Create with API Controller:

```
php artisan make:module Product --controller=Admin --api --requests
```

This adds:
- `app/Http/Controllers/Api/V1/Admin/ProductController.php`

The controller will include:
- Type-hinted `StoreProductRequest` and `UpdateProductRequest`
- Service-based logic
- Resource response using `StatusHelper`
- Auto-loading model relations for `show` method
- Returns wrapped `ProductResource`

---

### Generated Resource:

Auto-created by default:

- `app/Http/Resources/ProductResource.php`

With support for:
- Date formatting via `StatusHelper::formatDates()`
- Boolean status via `StatusHelper::getStatus()`
- Eloquent relationships using `whenLoaded()` and nested Resources

---

## ✅ Dependencies

This package publishes a helper class `App\Helpers\StatusHelper` which handles:
- Boolean status formatting
- Date/time formatting (incl. Persian date)
- Common API responses (success/error)

---

## 🔄 Overriding Published Files

You can publish/update helper files using:

```
php artisan module:publish --force
```

---

## 💡 Coming Soon

- Blade views
- Events/Observers
- Tests & factories
- GitHub Action integration

---

Made with ❤️ by [efati](https://github.com/AfshinEfati)
