# Test Generation

[🇮🇷 فارسی](../../fa/features/test-generation.md){ .language-switcher }

## Introduction

The generator automatically creates complete tests for your module's CRUD operations. The new version prioritizes Model Factories to create more reliable and realistic tests.

## New Features

### 1. Factory-First Approach

```php
// If Factory exists
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
            // Fallback to old method
        }
    }
    
    // Old method if Factory doesn't exist
}
```

## Usage

### Generate Tests

```bash
# Generate with tests
php artisan make:module Product --tests

# Or with all features
php artisan make:module Product --all
```

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific module tests
php artisan test --filter=ProductCrudTest

# With more details
php artisan test --filter=ProductCrudTest --testdox
```

## Generated Test Structure

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
    
    public function test_can_list_products(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(3)->create();
        
        $response = $this->actingAs($user)
            ->getJson($this->baseUri);
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
    
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
}
```

## Creating Factories

```bash
php artisan make:factory ProductFactory --model=Product
```

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'is_active' => $this->faker->boolean(80),
        ];
    }
    
    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}
```

## Advanced Testing Patterns

### 1. Testing with Relationships

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
                'category' => ['id', 'name'],
                'reviews' => []
            ]
        ]);
}
```

### 2. Testing Filters

```php
public function test_can_filter_products(): void
{
    Product::factory()->create(['price' => 100, 'is_active' => true]);
    Product::factory()->create(['price' => 500, 'is_active' => true]);
    
    $response = $this->actingAs($this->user)
        ->getJson('/api/products?min_price=200&is_active=true');
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
}
```

### 3. Testing Authorization

```php
public function test_unauthorized_user_cannot_create(): void
{
    $payload = Product::factory()->make()->toArray();
    
    $response = $this->postJson('/api/products', $payload);
    
    $response->assertStatus(401);
}
```

## Database Configuration

### Using SQLite for Tests

In `phpunit.xml`:

```xml
<php>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

## Best Practices

### 1. Use setUp and tearDown

```php
class ProductCrudTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
}
```

### 2. Clear Error Messages

```php
$this->assertNotNull(
    $product->category,
    'Product must have a category'
);
```

## Learn More

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [HTTP Tests](https://laravel.com/docs/http-tests)
- [Database Testing](https://laravel.com/docs/database-testing)
- [Factories](https://laravel.com/docs/eloquent-factories)
