# تولید Policy (مجوزدهی)

<div dir="rtl" markdown="1">

[🇬🇧 English](../../en/features/policy-generation.md){ .language-switcher }

## معرفی

با استفاده از فلگ `--policy`، می‌توانید به صورت خودکار کلاس‌های Policy برای مجوزدهی (Authorization) ماژول خود تولید کنید. این Policy‌ها شامل متدهای استاندارد CRUD برای کنترل دسترسی کاربران هستند.

## نحوه استفاده

### تولید ساده Policy

```bash
php artisan make:module Product --policy
```

این دستور یک فایل `ProductPolicy.php` در مسیر `app/Policies/` ایجاد می‌کند.

### تولید با سایر امکانات

```bash
# تولید کامل با Policy
php artisan make:module Product --all

# یا به صورت دستی
php artisan make:module Product --api --requests --tests --policy
```

## ساختار Policy تولیدشده

```php
<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * تعیین اینکه آیا کاربر می‌تواند لیست را مشاهده کند.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند مدل را مشاهده کند.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند مدل جدید ایجاد کند.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند مدل را بروزرسانی کند.
     */
    public function update(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند مدل را حذف کند.
     */
    public function delete(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند مدل را بازیابی کند.
     */
    public function restore(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند مدل را به صورت دائم حذف کند.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return true;
    }
}
```

## سفارشی‌سازی Policy

### بر اساس نقش کاربر

```php
public function create(User $user): bool
{
    return $user->role === 'admin' || $user->role === 'editor';
}
```

### بر اساس مالکیت

```php
public function update(User $user, Product $product): bool
{
    return $user->id === $product->user_id;
}
```

### بر اساس شرایط پیچیده

```php
public function delete(User $user, Product $product): bool
{
    // فقط ادمین‌ها یا صاحب محصول می‌توانند حذف کنند
    // و محصول نباید فروخته شده باشد
    return ($user->isAdmin() || $user->id === $product->user_id) 
        && !$product->is_sold;
}
```

## استفاده در Controller

لاراول به صورت خودکار Policy‌ها را تشخیص می‌دهد:

```php
class ProductController extends Controller
{
    public function update(UpdateProductRequest $request, Product $product)
    {
        // لاراول خودکار متد update از ProductPolicy را چک می‌کند
        $this->authorize('update', $product);
        
        // منطق بروزرسانی...
    }
    
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        // منطق حذف...
    }
}
```

## استفاده در Blade

```blade
@can('create', App\Models\Product::class)
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        ایجاد محصول جدید
    </a>
@endcan

@can('update', $product)
    <a href="{{ route('products.edit', $product) }}" class="btn">ویرایش</a>
@endcan

@can('delete', $product)
    <form action="{{ route('products.destroy', $product) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">حذف</button>
    </form>
@endcan
```

## استفاده در API

```php
// در FormRequest
public function authorize(): bool
{
    return $this->user()->can('create', Product::class);
}
```

```php
// در Resource
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'can_update' => $request->user()?->can('update', $this->resource),
            'can_delete' => $request->user()?->can('delete', $this->resource),
        ];
    }
}
```

## پیکربندی مسیر Policy

مسیر پیش‌فرض: `app/Policies/`

برای تغییر مسیر، می‌توانید در `config/module-generator.php` تنظیم کنید:

```php
'paths' => [
    'policy' => 'Policies',
    // یا
    'policy' => 'Domain/Authorization/Policies',
],
```

## نکات مهم

### 1. ثبت خودکار

لاراول 8+ به صورت خودکار Policy‌ها را تشخیص می‌دهد. نیازی به ثبت دستی نیست.

### 2. مدل User سفارشی

اگر مدل User شما در مسیر دیگری قرار دارد:

```php
// Policy به صورت خودکار با namespace صحیح تولید می‌شود
use App\Models\User;
// یا
use App\Domain\Users\User;
```

### 3. Guest Users

برای کاربران مهمان:

```php
public function viewAny(?User $user): bool
{
    // مهمان‌ها هم می‌توانند لیست را ببینند
    return true;
}

public function create(?User $user): bool
{
    // فقط کاربران لاگین‌شده
    return $user !== null;
}
```

## مثال‌های کاربردی

### 1. سیستم بلاگ

```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        // نویسنده یا ادمین می‌تواند ویرایش کند
        return $user->id === $post->author_id || $user->isAdmin();
    }
    
    public function publish(User $user, Post $post): bool
    {
        // فقط ادمین می‌تواند منتشر کند
        return $user->isAdmin();
    }
}
```

### 2. سیستم فروشگاه

```php
class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        // مشتری خودش یا ادمین
        return $user->id === $order->customer_id || $user->isAdmin();
    }
    
    public function cancel(User $user, Order $order): bool
    {
        // فقط اگر هنوز ارسال نشده باشد
        return $user->id === $order->customer_id 
            && $order->status === 'pending';
    }
}
```

### 3. سیستم چند نقشی

```php
class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['viewer', 'editor', 'admin']);
    }
    
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['editor', 'admin']);
    }
    
    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole('admin');
    }
}
```

## رفع مشکلات رایج

### Policy اعمال نمی‌شود

مطمئن شوید که:
- نام Policy با الگوی `{Model}Policy` مطابقت دارد
- Policy در مسیر `app/Policies/` قرار دارد
- کش را پاک کنید: `php artisan optimize:clear`

### دسترسی همیشه رد می‌شود

```php
// در AuthServiceProvider.php (در صورت نیاز)
protected $policies = [
    Product::class => ProductPolicy::class,
];
```

## منابع بیشتر

- [راهنمای رسمی Authorization لاراول](https://laravel.com/docs/authorization)
- [مستندات Policy](https://laravel.com/docs/authorization#creating-policies)
- [Gates و Policies](https://laravel.com/docs/authorization#gates-vs-policies)

</div>
```
