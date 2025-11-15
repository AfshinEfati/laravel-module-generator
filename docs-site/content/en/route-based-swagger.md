---
title: Route-Based Swagger Documentation
description: Automatic Swagger API documentation generation from routes
---

# Route-Based Swagger Documentation

Laravel Module Generator includes automatic API documentation generation using Swagger/OpenAPI specification.

## Overview

Routes defined in your modules are automatically documented with:

- Endpoint paths and HTTP methods
- Request parameters and body schemas
- Response schemas
- Authentication requirements
- Error responses

## Generating Documentation

### Automatic Generation

When you generate a module with the generator, Swagger documentation is created automatically:

```bash
php artisan make:module Blog --fields=title,content,author_id
# Automatically generates Swagger docs for:
# POST /api/blogs
# GET /api/blogs
# GET /api/blogs/{id}
# PUT /api/blogs/{id}
# DELETE /api/blogs/{id}
```

### Manual Generation

```bash
php artisan generate:swagger
```

This scans all routes and generates/updates Swagger documentation.

## Documentation Structure

### Endpoints

Each generated endpoint includes:

```yaml
paths:
  /api/blogs:
    get:
      summary: List all blogs
      tags: [Blogs]
      parameters:
        - name: page
          in: query
          type: integer
      responses:
        200:
          description: Success
          schema:
            type: array
            items:
              $ref: "#/components/schemas/Blog"

    post:
      summary: Create a blog
      tags: [Blogs]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/CreateBlogRequest"
      responses:
        201:
          description: Created
          schema:
            $ref: "#/components/schemas/Blog"
        422:
          description: Validation failed
```

## Viewing Documentation

### Web Interface

Access Swagger UI:

```bash
php artisan serve
# Visit http://localhost:8000/api/documentation
```

### JSON/YAML Export

```bash
# Export as JSON
php artisan swagger:export --format=json > swagger.json

# Export as YAML
php artisan swagger:export --format=yaml > swagger.yaml
```

## Customization

### Adding Descriptions

Add docs to your controller actions:

```php
/**
 * @OA\Get(
 *     path="/api/blogs/{id}",
 *     summary="Get a blog post",
 *     tags={"Blogs"},
 *     @OA\Parameter(name="id", in="path", required=true),
 *     @OA\Response(response=200, description="Success")
 * )
 */
public function show(Blog $blog)
{
    return new BlogResource($blog);
}
```

### Model Schemas

Define your models as schemas:

```php
/**
 * @OA\Schema(
 *     schema="Blog",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="content", type="string"),
 *     @OA\Property(property="published_at", type="string", format="date-time")
 * )
 */
class Blog extends Model
{
}
```

## Configuration

Swagger generation is configured in `config/module-generator.php`:

```php
'swagger' => [
    'enabled' => true,
    'title' => 'Laravel Module Generator API',
    'version' => '1.0.0',
    'base_path' => '/api',
    'schemes' => ['https'],
    'info' => [
        'description' => 'Generated API Documentation',
        'termsOfService' => 'https://example.com/terms',
        'contact' => [
            'name' => 'Support',
            'email' => 'support@example.com',
        ],
    ],
],
```

## Features

### Automatic Type Detection

- String fields → string schema
- Numeric fields → number schema
- Date/datetime fields → date-time format
- Foreign keys → relationship references
- Boolean fields → boolean schema

### Request/Response Schemas

Automatically derived from:

- Form request validation rules
- Model attributes
- Resource classes
- DTO structures

### Authentication

Documented automatically from:

- Route middleware
- Policy requirements
- Guard configuration

## OpenAPI 3.0 Support

Generated documentation follows OpenAPI 3.0 specification:

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API",
    "version": "1.0.0"
  },
  "paths": { ... },
  "components": {
    "schemas": { ... }
  }
}
```

## Publishing to Third-Party Services

### Swagger Hub

Export and upload to [Swagger Hub](https://swagger.io/):

```bash
php artisan swagger:export --format=json
# Upload to https://hub.swagger.io
```

### API Docs

Generate beautiful API documentation:

```bash
php artisan swagger:docs
```

## Best Practices

1. **Keep annotations in sync** - Update when routes change
2. **Use descriptive summaries** - Help API users understand endpoints
3. **Document error responses** - Include validation error examples
4. **Include examples** - Provide realistic request/response examples
5. **Version your API** - Update version when making breaking changes
