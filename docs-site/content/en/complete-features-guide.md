---
title: Complete Features Guide
description: In-depth guide to all features of Laravel Module Generator
---

# Complete Features Guide

A comprehensive overview of all features available in Laravel Module Generator.

## Core Features

### 1. Modular Architecture Generation

Automatically generate well-organized modular structures following clean architecture principles.

- Automatic namespace setup
- Proper folder organization
- Service layer implementation
- Repository pattern support
- DTO layer generation

### 2. Database-First Development

Define your module structure through database fields:

```bash
php artisan make:module Article --fields=title:string,content:text,author_id:foreign,published:boolean
```

Features:

- Migration generation
- Factory creation
- Seeder support
- Relationship handling

### 3. Schema-Aware Code Generation

The generator analyzes your field definitions and creates appropriate validation, casting, and serialization.

```bash
# String field
--fields=name:string => nullable, max:255 validation

# Numeric field
--fields=age:integer => numeric validation

# Date field
--fields=published_at:dateTime => date_format validation
```

### 4. Request Validation

Automatically generated form requests with smart validation:

```php
// Generated StoreBlogRequest.php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'published_at' => 'nullable|date_format:Y-m-d H:i:s',
    ];
}
```

### 5. API Resources

Automatically creates resource classes for API responses:

```php
// Generated BlogResource.php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'content' => $this->content,
        'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
    ];
}
```

### 6. Service Layer

Clean service classes for business logic:

```php
// Generated BlogService.php
public function store(BlogDTO $dto)
{
    return $this->repository->store($dto);
}

public function update($id, BlogDTO $dto)
{
    return $this->repository->update($id, $dto);
}
```

### 7. Repository Pattern

Data access layer abstraction:

```php
// Generated BlogRepository.php
public function all($perPage = 15)
{
    return $this->model->paginate($perPage);
}

public function store(BlogDTO $dto)
{
    return $this->model->create($dto->toArray());
}
```

### 8. Data Transfer Objects (DTOs)

Type-safe data containers:

```php
// Generated BlogDTO.php
class BlogDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public ?Carbon $published_at = null,
    ) {}
}
```

### 9. Automatic Testing

Generated test stubs:

```php
// Generated BlogTest.php
public function test_can_create_blog()
{
    $response = $this->post('/api/blogs', $this->validData());
    $response->assertCreated();
}
```

### 10. API Documentation

Swagger/OpenAPI documentation generation included.

## Advanced Features

### Jalali Date Support

Seamless Gregorian/Jalali date conversion with `HasGoliDates` trait.

### Action Layer

Additional action classes for complex operations:

```bash
php artisan make:module Blog --with-actions
```

### Policy Generation

Authorization policies for module resources:

```bash
php artisan make:module Post --with-policies
```

### Web UI

Interactive module generator with web interface:

```bash
php artisan serve
# Visit http://localhost:8000/module-generator
```

## Configuration

All features can be configured in `config/module-generator.php`:

```php
return [
    'namespace' => 'Modules',
    'path' => base_path('modules'),
    'publish_migrations' => true,
    'publish_factories' => true,
];
```

## Best Practices

1. **Define fields clearly** - Use specific field types for better generation
2. **Use DTOs** - Leverage generated DTOs for type safety
3. **Write tests** - Use generated test stubs as starting points
4. **Document APIs** - Swagger generation helps API documentation
5. **Customize stubs** - Publish and modify stubs for your project standards

## Migration from Other Generators

If you're coming from other Laravel module generators:

1. Install Laravel Module Generator
2. Publish stubs: `php artisan vendor:publish --tag=module-generator-stubs`
3. Customize as needed
4. Generate your first module
5. Adjust generated files to match your patterns
