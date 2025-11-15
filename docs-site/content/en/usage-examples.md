---
title: Usage Examples
description: Practical examples and use cases for Laravel Module Generator
---

# Usage Examples

Learn how to use Laravel Module Generator with real-world examples.

## Basic Module Generation

Generate a simple blog module:

```bash
php artisan make:module Blog --fields=title,content,published_at
```

This creates:

- `Blog/Controllers/BlogController.php`
- `Blog/Services/BlogService.php`
- `Blog/DTOs/BlogDTO.php`
- `Blog/Requests/StoreBlogRequest.php`
- Database migration
- Tests

## Generate with Custom Namespace

```bash
php artisan make:module Admin\\Dashboard --fields=widget_type,config
```

## Generate Only Specific Components

```bash
# Only controller and service
php artisan make:module Products --only=controller,service

# Only DTO and request
php artisan make:module Orders --only=dto,request
```

## Using Generated Module

After generation, your module is ready to use:

```php
use Modules\Blog\Services\BlogService;
use Modules\Blog\DTOs\BlogDTO;

$service = new BlogService();
$dto = new BlogDTO(
    title: 'My Post',
    content: 'Post content...',
    published_at: now()
);

$blog = $service->store($dto);
```

## Database Operations

The generated repository provides database abstraction:

```php
use Modules\Blog\Repositories\BlogRepository;

$repo = new BlogRepository();
$posts = $repo->all();
$post = $repo->find(1);
$post = $repo->store($dto);
$repo->update(1, $dto);
$repo->delete(1);
```

## Testing Generated Module

Tests are automatically generated:

```bash
phpunit tests/Feature/BlogTest.php
```

## Publishing Custom Stubs

Customize generated files:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

Edit stubs in `resources/stubs/modules/` and regenerate modules.
