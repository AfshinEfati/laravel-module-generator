# Policy Generation

[🇮🇷 فارسی](../../fa/features/policy-generation.md){ .language-switcher }

## Introduction

Using the `--policy` flag, you can automatically generate Policy classes for authorization in your module. These Policies include standard CRUD methods for controlling user access.

## Usage

### Basic Policy Generation

```bash
php artisan make:module Product --policy
```

This creates a `ProductPolicy.php` file in `app/Policies/`.

### Generate with Other Features

```bash
# Full generation with Policy
php artisan make:module Product --all

# Or manually
php artisan make:module Product --api --requests --tests --policy
```

## Generated Policy Structure

```php
<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Product $product): bool
    {
        return true;
    }

    public function delete(User $user, Product $product): bool
    {
        return true;
    }

    public function restore(User $user, Product $product): bool
    {
        return true;
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return true;
    }
}
```

## Customizing Policies

### Based on User Role

```php
public function create(User $user): bool
{
    return $user->role === 'admin' || $user->role === 'editor';
}
```

### Based on Ownership

```php
public function update(User $user, Product $product): bool
{
    return $user->id === $product->user_id;
}
```

### Complex Conditions

```php
public function delete(User $user, Product $product): bool
{
    return ($user->isAdmin() || $user->id === $product->user_id) 
        && !$product->is_sold;
}
```

## Using in Controllers

```php
class ProductController extends Controller
{
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        
        // Update logic...
    }
}
```

## Using in Blade

```blade
@can('create', App\Models\Product::class)
    <a href="{{ route('products.create') }}">Create Product</a>
@endcan

@can('update', $product)
    <a href="{{ route('products.edit', $product) }}">Edit</a>
@endcan
```

## Learn More

- [Laravel Authorization Docs](https://laravel.com/docs/authorization)
- [Policy Documentation](https://laravel.com/docs/authorization#creating-policies)
