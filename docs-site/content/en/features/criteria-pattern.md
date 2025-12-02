# Criteria Pattern

[🇮🇷 فارسی](../../fa/features/criteria-pattern.md){ .language-switcher }

## Introduction

The Criteria Pattern is a powerful way to build reusable filters in your repositories. Instead of writing repetitive queries, you can define your filters in separate classes and use them anywhere.

## Benefits

- **Reusable**: Write once, use everywhere
- **Testable**: Test each Criteria independently
- **Clean**: Break complex code into smaller pieces
- **Composable**: Combine multiple Criteria together

## Basic Structure

### 1. Criteria Interface

```php
<?php

namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

interface CriteriaInterface
{
    public function apply(Builder $model): Builder;
}
```

### 2. Creating Custom Criteria

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

## Using Criteria

### In Controller or Service

```php
use App\Repositories\Criteria\ActiveProductsCriteria;

$products = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->getAll();
```

### Combining Multiple Criteria

```php
$products = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->pushCriteria(new FeaturedCriteria())
    ->pushCriteria(new InStockCriteria())
    ->getAll();
```

## Common Criteria Examples

### 1. Date Filter

```php
class CreatedAfterCriteria implements CriteriaInterface
{
    public function __construct(private string $date) {}
    
    public function apply(Builder $model): Builder
    {
        return $model->where('created_at', '>=', $this->date);
    }
}
```

### 2. Price Range

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
```

### 3. Search

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
```

## Combining with findDynamic

Criteria works with `findDynamic` and `getByDynamic`:

```php
$product = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->findDynamic(
        where: ['slug' => 'iphone-15'],
        with: ['reviews']
    );
```

## Managing Criteria

### Remove Criteria

```php
$this->repository->popCriteria(ActiveProductsCriteria::class);
```

### Skip Criteria Temporarily

```php
$allProducts = $this->repository
    ->pushCriteria(new ActiveProductsCriteria())
    ->skipCriteria()
    ->getAll();
```

## Learn More

- [Repository Pattern](https://designpatternsphp.readthedocs.io/en/latest/More/Repository/README.html)
- [Specification Pattern](https://en.wikipedia.org/wiki/Specification_pattern)
