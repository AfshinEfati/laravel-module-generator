# 🔗 Integration Guide: Optional External Packages

## نمایش‌ کامل

Laravel Module Generator **بطور مستقل** PHPDoc documentation تولید می‌کند. اگر می‌خواهید **UI و Processing** اضافی استفاده کنید، می‌توانید optional packages نصب کنید.

---

## ✅ Standalone (Default)

```bash
php artisan make:module Product --swagger
# ✅ Generates: app/Docs/ProductDoc.php
# ✅ No dependencies needed
```

---

## 🔧 With Swagger-PHP Package

### Installation

```bash
composer require zircote/swagger-php
```

### Usage

#### Option 1: Process Generated Files

```bash
# Generate PHPDoc files
php artisan make:swagger --force

# Process with swagger-php
./vendor/bin/openapi --output public/docs/api.json app/Docs/

# View spec
cat public/docs/api.json | jq '.'
```

#### Option 2: Laravel Wrapper Command

Create an Artisan command that wraps swagger-php:

```bash
# File: app/Console/Commands/SwaggerProcess.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwaggerProcess extends Command
{
    protected $signature = 'swagger:process';
    protected $description = 'Process PHPDoc files with swagger-php';

    public function handle()
    {
        $output = base_path('public/docs/api.json');
        $path = app_path('Docs');

        $command = "./vendor/bin/openapi --output {$output} {$path}";

        $this->line("Processing: $path");
        passthru($command);

        $this->info("Generated: $output");
    }
}
```

Then:

```bash
php artisan swagger:process
```

### Result

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API Documentation",
    "version": "1.0.0"
  },
  "paths": {
    "/api/products": { ... },
    "/api/products/{id}": { ... }
  }
}
```

---

## 🎨 With L5-Swagger

### Installation

```bash
composer require darkaonline/l5-swagger
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="L5\\Swagger\\SwaggerServiceProvider"
```

### Configuration

Edit `config/l5-swagger.php`:

```php
'paths' => [
    'docs' => storage_path('api-docs'),
    'docs_json' => 'api-docs.json',
    'docs_yaml' => 'api-docs.yaml',
    'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
],

'operations' => [
    'consumes' => ['application/json'],
    'produces' => ['application/json'],
],

'routes' => [
    'api' => '/api/documentation',
    'docs' => '/docs',
],
```

### Workflow

#### Step 1: Generate PHPDoc Files

```bash
php artisan make:module Product --swagger
# Creates: app/Docs/ProductDoc.php
```

#### Step 2: Generate Swagger Docs with L5

```bash
php artisan l5-swagger:generate
# Creates: storage/api-docs/api-docs.json
# and: storage/api-docs/api-docs.yaml
```

#### Step 3: View UI

Visit: `http://localhost:8000/docs`

---

## 📋 Workflow Comparison

### Standalone (No Dependencies)

```bash
# 1. Generate PHPDoc
php artisan make:swagger --force

# 2. Serve JSON UI
php artisan swagger:generate
php artisan swagger:ui

# Result: localhost:8000/docs
# Files: app/Docs/*, storage/app/swagger.json
```

**Pros:**
- ✅ No external dependencies
- ✅ Built-in theme customization
- ✅ Zero configuration
- ✅ Works out of box

**Cons:**
- ⚠️ Limited UI customization
- ⚠️ No YAML support
- ⚠️ Only JSON format

---

### With Swagger-PHP

```bash
# 1. Generate PHPDoc
php artisan make:swagger --force

# 2. Process with swagger-php
./vendor/bin/openapi --output public/docs/api.json app/Docs/

# 3. Use JSON elsewhere
# Result: public/docs/api.json
```

**Pros:**
- ✅ Standard OpenAPI format
- ✅ Can use generated spec with any tool
- ✅ CLI tools support
- ✅ CI/CD integration

**Cons:**
- ⚠️ Extra dependency
- ⚠️ Manual processing
- ⚠️ No built-in UI

---

### With L5-Swagger

```bash
# 1. Generate PHPDoc
php artisan make:module Product --swagger

# 2. Generate with L5
php artisan l5-swagger:generate

# 3. View UI
# Result: localhost:8000/docs
# Files: storage/api-docs/*, app/Docs/*
```

**Pros:**
- ✅ Built-in Swagger UI
- ✅ Both JSON and YAML
- ✅ Production-ready
- ✅ Well-maintained

**Cons:**
- ⚠️ Extra dependency
- ⚠️ Heavier package
- ⚠️ Configuration overhead

---

## 🚀 Complete Example: With L5-Swagger

### Step 1: Install

```bash
composer require efati/laravel-module-generator
composer require darkaonline/l5-swagger
```

### Step 2: Publish

```bash
# L5-Swagger config
php artisan vendor:publish --provider="L5\\Swagger\\SwaggerServiceProvider"
```

### Step 3: Generate Module

```bash
php artisan make:module Product -a --swagger --tests
```

**Creates:**
- `app/Repositories/Eloquent/ProductRepository.php`
- `app/Services/ProductService.php`
- `app/Http/Controllers/Api/ProductController.php`
- `app/Http/Requests/StoreProductRequest.php`
- `app/Http/Resources/ProductResource.php`
- `app/Docs/ProductDoc.php` ← **Swagger annotations**
- `tests/Feature/ProductTest.php`

### Step 4: Generate Swagger UI

```bash
php artisan l5-swagger:generate
```

**Creates:**
- `storage/api-docs/api-docs.json`
- `storage/api-docs/api-docs.yaml`

### Step 5: View

```bash
# Open browser
php artisan serve
# Visit: http://localhost:8000/docs
```

### Step 6: Update Docs

Change `ProductDoc.php` and regenerate:

```bash
php artisan l5-swagger:generate
```

---

## 🔄 Migration Path

### Start Standalone

```bash
# No dependencies
php artisan make:module Product --swagger
```

### Later Add Swagger-PHP

```bash
composer require zircote/swagger-php
# Already have PHPDoc files, just process them
./vendor/bin/openapi --output public/docs/api.json app/Docs/
```

### Later Add L5-Swagger

```bash
composer require darkaonline/l5-swagger
# Already have PHPDoc files, just generate
php artisan l5-swagger:generate
```

---

## 📝 Custom Integration

### Use with OpenAPI Generators

Your generated `app/Docs/*.php` files can be processed by:

- **swagger-php** (PHP)
- **openapi-generator-cli** (JavaScript)
- **swagger-ui** (JavaScript)
- **redoc** (JavaScript)
- **dredd** (API testing)

### Example: With OpenAPI CLI

```bash
# Generate TypeScript client from spec
npm install -g openapi-generator-cli

# First generate JSON
./vendor/bin/openapi --output swagger.json app/Docs/

# Then generate client
openapi-generator-cli generate \
  -i swagger.json \
  -g typescript \
  -o client/
```

---

## ❓ FAQ

**Q: Can I use both standalone UI and L5-Swagger?**

A: Yes, but they'll serve different specs. Keep them synced by running both:

```bash
php artisan make:swagger --force        # PHPDoc files
php artisan swagger:generate             # JSON for standalone UI
php artisan l5-swagger:generate          # JSON/YAML for L5-Swagger
```

**Q: Which approach should I use?**

| Requirement | Solution |
|---|---|
| No dependencies | Standalone ✅ |
| Full API spec | With Swagger-PHP ✅ |
| Production UI | With L5-Swagger ✅ |
| CI/CD processing | With Swagger-PHP ✅ |
| Quick setup | Standalone ✅ |

**Q: Can I add more routes after initial generation?**

A: Yes, regenerate anytime:

```bash
php artisan make:swagger --force
php artisan l5-swagger:generate
```

**Q: How do I customize generated docs?**

Edit `app/Docs/ProductDoc.php` directly:

```php
/**
 * @OA\Get(
 *     path="/api/products",
 *     summary="List all products",
 *     description="Returns paginated list of products",
 *     @OA\Response(response=200, description="Success")
 * )
 */
public function list() {}
```

---

## 📚 Related Documentation

- [Swagger No Dependencies](SWAGGER_NO_DEPENDENCIES.md)
- [PHPDoc Generation](SWAGGER_PHPDOC_GENERATION.md)
- [Configuration Guide](SWAGGER_CONFIG.md)
- [Command Reference](COMMAND_REFERENCE.md)

---

**خلاصه:**
- ✅ **شروع کنید:** بدون dependency (Standalone)
- ✅ **زمانی** که لازم شد: swagger-php نصب کنید
- ✅ **برای Production:** l5-swagger استفاده کنید
