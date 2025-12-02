# الگوی Criteria (فیلترسازی پیشرفته)

<div dir="rtl" markdown="1">

[🇬🇧 English](../../en/features/criteria-pattern.md){ .language-switcher }

## معرفی

الگوی Criteria یک روش قدرتمند برای ساخت فیلترهای قابل استفاده مجدد در ریپازیتوری‌ها است. به جای نوشتن کوئری‌های تکراری، می‌توانید فیلترهای خود را در کلاس‌های جداگانه تعریف کرده و در جاهای مختلف استفاده کنید.

## مزایای Criteria

- **قابل استفاده مجدد**: یک بار بنویس، در همه‌جا استفاده کن
- **تست‌پذیر**: هر Criteria را به صورت مستقل تست کنید
- **تمیز و خوانا**: کدهای پیچیده را به بخش‌های کوچک تقسیم کنید
- **ترکیب‌پذیر**: چندین Criteria را با هم ترکیب کنید

## ساختار پایه

### 1. رابط Criteria

```php
<?php

namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

interface CriteriaInterface
{
    /**
     * اعمال Criteria به کوئری
     */
    public function apply(Builder $model): Builder;
}
```

### 2. ایجاد Criteria سفارشی

```php
<?php

namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

class ActiveProductsCriteria implements CriteriaInterface
{
    public function apply(Builder $model): Builder
    {
        return $model->where('is_active', true);
    }
}
```

## استفاده از Criteria

### در Controller یا Service

```php
use App\Repositories\Criteria\ActiveProductsCriteria;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}
    
    public function index()
    {
        $products = $this->repository
            ->pushCriteria(new ActiveProductsCriteria())
            ->getAll();
            
        return ProductResource::collection($products);
    }
}
```

### ترکیب چندین Criteria

```php
use App\Repositories\Criteria\ActiveProductsCriteria;
use App\Repositories\Criteria\FeaturedCriteria;
use App\Repositories\Criteria\InStockCriteria;

$products = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->pushCriteria(new FeaturedCriteria())
    ->pushCriteria(new InStockCriteria())
    ->getAll();
```

## مثال‌های Criteria متداول

### 1. فیلتر بر اساس تاریخ

```php
class CreatedAfterCriteria implements CriteriaInterface
{
    public function __construct(
        private string $date
    ) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->where('created_at', '>=', $this->date);
    }
}

// استفاده
$products = $this->repository
    ->pushCriteria(new CreatedAfterCriteria('2024-01-01'))
    ->getAll();
```

### 2. فیلتر بر اساس دسته‌بندی

```php
class ByCategoryCriteria implements CriteriaInterface
{
    public function __construct(
        private int $categoryId
    ) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->where('category_id', $this->categoryId);
    }
}
```

### 3. فیلتر قیمتی

```php
class PriceRangeCriteria implements CriteriaInterface
{
    public function __construct(
        private ?float $minPrice = null,
        private ?float $maxPrice = null
    ) {}
    
    public function apply(Builder $model): Builder
    {
        if ($this->minPrice !== null) {
            $model->where('price', '>=', $this->minPrice);
        }
        
        if ($this->maxPrice !== null) {
            $model->where('price', '<=', $this->maxPrice);
        }
        
        return $model;
    }
}

// استفاده
$products = $this->repository
    ->pushCriteria(new PriceRangeCriteria(100, 500))
    ->getAll();
```

### 4. جستجو در چند فیلد

```php
class SearchCriteria implements CriteriaInterface
{
    public function __construct(
        private string $query,
        private array $fields = ['name', 'description']
    ) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->where(function ($query) {
            foreach ($this->fields as $field) {
                $query->orWhere($field, 'LIKE', "%{$this->query}%");
            }
        });
    }
}

// استفاده
$products = $this->repository
    ->pushCriteria(new SearchCriteria('laptop', ['name', 'description', 'tags']))
    ->getAll();
```

### 5. مرتب‌سازی

```php
class OrderByCriteria implements CriteriaInterface
{
    public function __construct(
        private string $column = 'created_at',
        private string $direction = 'desc'
    ) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->orderBy($this->column, $this->direction);
    }
}
```

### 6. Eager Loading

```php
class WithRelationsCriteria implements CriteriaInterface
{
    public function __construct(
        private array $relations
    ) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->with($this->relations);
    }
}

// استفاده
$products = $this->repository
    ->pushCriteria(new WithRelationsCriteria(['category', 'tags', 'images']))
    ->getAll();
```

## ترکیب با findDynamic

Criteria با متدهای `findDynamic` و `getByDynamic` نیز کار می‌کند:

```php
$product = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->findDynamic(
        where: ['slug' => 'iphone-15'],
        with: ['reviews']
    );
```

## مدیریت Criteria

### حذف Criteria

```php
// حذف یک Criteria خاص
$this->repository->popCriteria(ActiveProductsCriteria::class);

// یا با instance
$criteria = new ActiveProductsCriteria();
$this->repository
    ->pushCriteria($criteria)
    ->popCriteria($criteria);
```

### نادیده گرفتن موقت Criteria

```php
// نادیده گرفتن تمام Criteria‌ها برای این کوئری
$allProducts = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->skipCriteria()
    ->getAll();

// برگرداندن حالت عادی
$activeProducts = $this->repository
    ->skipCriteria(false)
    ->getAll();
```

### مشاهده Criteria‌های فعال

```php
$activeCriteria = $this->repository->getCriteria();
```

## مثال پیشرفته: فیلتر پویا از Request

```php
class ProductFilterCriteria implements CriteriaInterface
{
    public function __construct(
        private array $filters
    ) {}
    
    public function apply(Builder $model): Builder
    {
        if (!empty($this->filters['category'])) {
            $model->where('category_id', $this->filters['category']);
        }
        
        if (!empty($this->filters['min_price'])) {
            $model->where('price', '>=', $this->filters['min_price']);
        }
        
        if (!empty($this->filters['max_price'])) {
            $model->where('price', '<=', $this->filters['max_price']);
        }
        
        if (!empty($this->filters['search'])) {
            $model->where(function ($query) {
                $query->where('name', 'LIKE', "%{$this->filters['search']}%")
                      ->orWhere('description', 'LIKE', "%{$this->filters['search']}%");
            });
        }
        
        if (!empty($this->filters['sort'])) {
            $direction = $this->filters['sort_direction'] ?? 'asc';
            $model->orderBy($this->filters['sort'], $direction);
        }
        
        return $model;
    }
}

// استفاده در Controller
public function index(Request $request)
{
    $products = $this->repository
        ->pushCriteria(new ProductFilterCriteria($request->all()))
        ->getAll();
        
    return ProductResource::collection($products);
}
```

## Criteria برای Scope‌های لاراول

```php
class PublishedCriteria implements CriteriaInterface
{
    public function apply(Builder $model): Builder
    {
        // استفاده از scope مدل
        return $model->published();
    }
}
```

## نکات مهم

### 1. Stateless باشند

Criteria‌ها نباید state داشته باشند (به جز constructor parameters):

```php
// ✅ درست
class StatusCriteria implements CriteriaInterface
{
    public function __construct(private string $status) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->where('status', $this->status);
    }
}

// ❌ غلط
class StatusCriteria implements CriteriaInterface
{
    private string $status;
    
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}
```

### 2. نام‌گذاری واضح

```php
// ✅ درست
ActiveProductsCriteria
PublishedPostsCriteria
OrderByPriceCriteria

// ❌ غلط  
ProductCriteria
FilterCriteria
Criteria1
```

### 3. مسئولیت واحد

هر Criteria فقط یک مسئولیت داشته باشد:

```php
// ✅ درست - دو Criteria جدا
$products = $this->repository
    ->pushCriteria(new ActiveCriteria())
    ->pushCriteria(new InStockCriteria())
    ->getAll();

// ❌ غلط - همه چیز در یک Criteria
$products = $this->repository
    ->pushCriteria(new ActiveAndInStockAndFeaturedCriteria())
    ->getAll();
```

## تست کردن Criteria

```php
use Tests\TestCase;
use App\Repositories\Criteria\ActiveProductsCriteria;
use App\Models\Product;

class ActiveProductsCriteriaTest extends TestCase
{
    public function test_filters_active_products()
    {
        // Arrange
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);
        
        // Act
        $criteria = new ActiveProductsCriteria();
        $query = Product::query();
        $result = $criteria->apply($query)->get();
        
        // Assert
        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is_active);
    }
}
```

## منابع بیشتر

- [الگوی Repository](https://designpatternsphp.readthedocs.io/en/latest/More/Repository/README.html)
- [الگوی Specification](https://en.wikipedia.org/wiki/Specification_pattern)

</div>
```
