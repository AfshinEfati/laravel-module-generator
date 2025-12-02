# تولید تست‌های خودکار

<div dir="rtl" markdown="1">

[🇬🇧 English](../../en/features/test-generation.md){ .language-switcher }

## معرفی

ژنراتور به صورت خودکار تست‌های کامل برای عملیات CRUD ماژول شما تولید می‌کند. نسخه جدید با اولویت دادن به Model Factory‌ها، تست‌های قابل اطمینان‌تر و واقعی‌تری می‌سازد.

## ویژگی‌های جدید

### 1. استفاده اولویت‌دار از Factory

```php
// اگر Factory وجود داشته باشد
private function buildValidPayload(bool $forCreate = true): array
{
    if (method_exists(\App\Models\Product::class, 'factory')) {
        try {
            $model = \App\Models\Product::factory()->make();
            $data = $model->toArray();
            
            $fillable = $this->fillable();
            $payload = array_intersect_key($data, array_flip($fillable));
            
            return [$payload, true];
        } catch (\Throwable $e) {
            // بازگشت به حالت پیش‌فرض
        }
    }
    
    // روش قدیمی در صورت عدم وجود Factory
    // ...
}
```

## نحوه استفاده

### تولید تست

```bash
# تولید با تست
php artisan make:module Product --tests

# یا به همراه همه امکانات
php artisan make:module Product --all
```

### اجرای تست‌ها

```bash
# اجرای تمام تست‌ها
php artisan test

# اجرای تست‌های یک ماژول خاص
php artisan test --filter=ProductCrudTest

# با جزئیات بیشتر
php artisan test --filter=ProductCrudTest --testdox
```

## ساختار تست تولیدشده

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUri = '/api/products';
    
    /**
     * تست لیست محصولات
     */
    public function test_can_list_products(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(3)->create();
        
        $response = $this->actingAs($user)
            ->getJson($this->baseUri);
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
    
    /**
     * تست نمایش یک محصول
     */
    public function test_can_show_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson("{$this->baseUri}/{$product->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ]
            ]);
    }
    
    /**
     * تست ایجاد محصول
     */
    public function test_can_create_product(): void
    {
        $user = User::factory()->create();
        $payload = Product::factory()->make()->toArray();
        
        $response = $this->actingAs($user)
            ->postJson($this->baseUri, $payload);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => $payload['name'],
        ]);
    }
    
    /**
     * تست بروزرسانی محصول
     */
    public function test_can_update_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $payload = ['name' => 'Updated Name'];
        
        $response = $this->actingAs($user)
            ->putJson("{$this->baseUri}/{$product->id}", $payload);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
        ]);
    }
    
    /**
     * تست حذف محصول
     */
    public function test_can_delete_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)
            ->deleteJson("{$this->baseUri}/{$product->id}");
        
        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
    
    /**
     * تست اعتبارسنجی
     */
    public function test_validation_errors_on_create(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson($this->baseUri, []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }
}
```

## ساخت Factory

برای استفاده بهینه از تست‌ها، حتماً Factory بسازید:

```bash
php artisan make:factory ProductFactory --model=Product
```

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'category_id' => Category::factory(),
            'is_active' => $this->faker->boolean(80),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
    
    /**
     * State برای محصول فعال
     */
    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
    
    /**
     * State برای محصول غیرفعال
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
    
    /**
     * State برای محصول موجود
     */
    public function inStock(): static
    {
        return $this->state(fn () => ['stock' => $this->faker->numberBetween(10, 100)]);
    }
}
```

## الگوهای تست پیشرفته

### 1. تست با روابط (Relationships)

```php
public function test_product_with_relations(): void
{
    $category = Category::factory()->create();
    $product = Product::factory()
        ->for($category)
        ->has(Review::factory()->count(3))
        ->create();
    
    $response = $this->actingAs($this->user)
        ->getJson("/api/products/{$product->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'category' => ['id', 'name'],
                'reviews' => []
            ]
        ]);
}
```

### 2. تست فیلترها

```php
public function test_can_filter_products(): void
{
    Product::factory()->create(['price' => 100, 'is_active' => true]);
    Product::factory()->create(['price' => 500, 'is_active' => true]);
    Product::factory()->create(['price' => 300, 'is_active' => false]);
    
    $response = $this->actingAs($this->user)
        ->getJson('/api/products?min_price=200&is_active=true');
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
}
```

### 3. تست صفحه‌بندی

```php
public function test_pagination_works(): void
{
    Product::factory()->count(25)->create();
    
    $response = $this->actingAs($this->user)
        ->getJson('/api/products?per_page=10');
    
    $response->assertStatus(200)
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'total', 'per_page']
        ]);
}
```

### 4. تست مجوزها (Authorization)

```php
public function test_unauthorized_user_cannot_create(): void
{
    $payload = Product::factory()->make()->toArray();
    
    $response = $this->postJson('/api/products', $payload);
    
    $response->assertStatus(401);
}

public function test_user_cannot_delete_others_product(): void
{
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->for($owner)->create();
    
    $response = $this->actingAs($otherUser)
        ->deleteJson("/api/products/{$product->id}");
    
    $response->assertStatus(403);
}
```

### 5. تست اعتبارسنجی پیشرفته

```php
/**
 * @dataProvider invalidDataProvider
 */
public function test_validation_with_data_provider($field, $value): void
{
    $user = User::factory()->create();
    $payload = Product::factory()->make()->toArray();
    $payload[$field] = $value;
    
    $response = $this->actingAs($user)
        ->postJson('/api/products', $payload);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors([$field]);
}

public static function invalidDataProvider(): array
{
    return [
        'name is null' => ['name', null],
        'name is empty' => ['name', ''],
        'name is too long' => ['name', str_repeat('a', 256)],
        'price is negative' => ['price', -10],
        'price is not numeric' => ['price', 'invalid'],
    ];
}
```

## تنظیمات Database

### استفاده از SQLite برای تست

در `phpunit.xml`:

```xml
<php>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
```

### Migration‌ها را اجرا کنید

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // اجرای Seeder در صورت نیاز
        $this->seed(CategorySeeder::class);
    }
}
```

## بهترین روش‌ها

### 1. استفاده از setUp و tearDown

```php
class ProductCrudTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    protected Category $category;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }
    
    public function test_example(): void
    {
        // $this->user و $this->category در دسترس هستند
    }
}
```

### 2. استفاده از Traits سفارشی

```php
trait WithAuthentication
{
    protected User $user;
    
    protected function authenticate(): void
    {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }
}

class ProductCrudTest extends TestCase
{
    use RefreshDatabase, WithAuthentication;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }
}
```

### 3. مشخص کردن پیام‌های خطا

```php
public function test_product_has_category(): void
{
    $product = Product::factory()->create();
    
    $this->assertNotNull(
        $product->category,
        'محصول باید حتماً یک دسته‌بندی داشته باشد'
    );
}
```

## Coverage و گزارش‌ها

### اجرا با Coverage

```bash
# نیاز به Xdebug یا PCOV
php artisan test --coverage

# با جزئیات بیشتر
php artisan test --coverage --min=80
```

### گزارش HTML

```bash
XDEBUG_MODE=coverage php artisan test --coverage-html reports/
```

## رفع مشکلات رایج

### تست‌ها اجرا نمی‌شوند

```bash
# پاک کردن کش
php artisan config:clear
php artisan cache:clear

# بررسی PHPUnit
./vendor/bin/phpunit --version

# اجرا با verbose
php artisan test --verbose
```

### خطای Database

```php
// مطمئن شوید RefreshDatabase استفاده می‌کنید
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;
}
```

### Factory پیدا نمی‌شود

```bash
# مطمئن شوید Factory ساخته‌اید
php artisan make:factory ProductFactory --model=Product

# Autoload را Refresh کنید
composer dump-autoload
```

## منابع بیشتر

- [راهنمای رسمی Testing لاراول](https://laravel.com/docs/testing)
- [HTTP Tests](https://laravel.com/docs/http-tests)
- [Database Testing](https://laravel.com/docs/database-testing)
- [Factories](https://laravel.com/docs/eloquent-factories)

</div>
