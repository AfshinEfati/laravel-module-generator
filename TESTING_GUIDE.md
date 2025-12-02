# 🧪 Testing Guide

Complete guide for testing Laravel Module Generator in your Laravel project.

---

## Prerequisites

- Laravel 10 or 11
- PHP 8.1+
- Composer installed

---

## Installation & Setup

### Step 1: Create Test Laravel Project

```bash
# Using Laravel installer
laravel new test-module-generator
cd test-module-generator

# Or using composer
composer create-project laravel/laravel test-module-generator
cd test-module-generator
```

### Step 2: Install Package

```bash
composer require efati/Laravel-Scaffolder
```

### Step 3: Verify Installation

```bash
php artisan list | grep make:
php artisan list | grep swagger
```

Expected output:
```
make:module              Generate a new module
make:swagger             Generate Swagger documentation
swagger:config           Configure Swagger
swagger:generate         Generate Swagger spec
swagger:init             Initialize Swagger UI
swagger:ui               Start Swagger UI server
```

---

## Basic Testing

### Test 1: Simple Module Generation

```bash
php artisan make:module Product
```

**Expected:**
- ✅ `app/Repositories/Contracts/ProductRepositoryContract.php`
- ✅ `app/Repositories/Eloquent/ProductRepository.php`
- ✅ `app/Services/Contracts/ProductServiceContract.php`
- ✅ `app/Services/ProductService.php`
- ✅ `app/DTOs/ProductDTO.php`
- ✅ `app/Providers/ProductServiceProvider.php`

**Verify:**
```bash
ls -la app/Repositories/
ls -la app/Services/
ls -la app/DTOs/
```

---

### Test 2: API Module with Swagger

```bash
php artisan make:module Post -a --swagger --tests
```

**Expected:**
- ✅ All files from Test 1
- ✅ `app/Http/Controllers/Api/PostController.php`
- ✅ `app/Http/Requests/StorePostRequest.php`
- ✅ `app/Http/Requests/UpdatePostRequest.php`
- ✅ `app/Http/Resources/PostResource.php`
- ✅ `app/Docs/PostDoc.php` ← **Swagger documentation**
- ✅ `tests/Feature/PostTest.php`

**Verify:**
```bash
php -l app/Docs/PostDoc.php
cat app/Docs/PostDoc.php | grep "@OA"
```

---

### Test 3: Check Swagger Doc Content

```bash
cat app/Docs/PostDoc.php
```

Expected output should contain:
```php
/**
 * @OA\Tag(name="Post")
 * ...
 */
class PostDoc
```

---

## Swagger Functionality Testing

### Test 4: Generate All Swagger Docs

```bash
# First create multiple modules
php artisan make:module Category -a --swagger
php artisan make:module Tag -a --swagger

# Then generate all docs
php artisan make:swagger --force
```

**Expected:**
- ✅ `app/Docs/PostDoc.php`
- ✅ `app/Docs/CategoryDoc.php`
- ✅ `app/Docs/TagDoc.php`

**Verify:**
```bash
ls -la app/Docs/
```

---

### Test 5: Initialize Swagger UI

```bash
php artisan swagger:init
```

**Expected:**
- ✅ `public/docs/` directory created
- ✅ `public/docs/index.html`
- ✅ `public/docs/swagger-ui.css`
- ✅ `public/docs/swagger-ui.bundle.js`
- ✅ Theme files created

**Verify:**
```bash
ls -la public/docs/
file public/docs/index.html
```

---

### Test 6: View Swagger UI

```bash
php artisan swagger:ui
```

This starts a dev server. Open your browser:
```
http://localhost:8000/docs
```

**Expected:**
- ✅ Swagger UI loads
- ✅ API endpoints visible
- ✅ Try-it-out functionality works

---

## Configuration Testing

### Test 7: Configure Swagger Interactively

```bash
php artisan swagger:config
```

Follow the interactive menu to:
- ✅ Select theme (vanilla/tailwind/dark)
- ✅ Choose colors
- ✅ Toggle dark mode
- ✅ Set fonts

**Result:** Creates/updates `.env` entries

---

### Test 8: View Configuration

```bash
php artisan swagger:config --show
```

Expected output:
```
┌─ Swagger Configuration ─────────────┐
│ Theme:           tailwind           │
│ Dark Mode:       Yes                │
│ Primary Color:   #3b82f6            │
│ Secondary Color: #64748b            │
└─────────────────────────────────────┘
```

---

### Test 9: Export Configuration to .env

```bash
php artisan swagger:config --export-env
```

**Expected:**
- ✅ `.env` file updated with SWAGGER_* variables
- ✅ Can be verified with: `grep SWAGGER .env`

---

### Test 10: Reset Configuration

```bash
php artisan swagger:config --reset
```

**Expected:**
- ✅ Configuration reset to defaults
- ✅ Verify with: `php artisan swagger:config --show`

---

## Schema Inference Testing

### Test 11: From Migration

```bash
# Create migration
php artisan make:migration create_products_table

# Edit migration
nano database/migrations/202*_create_products_table.php
```

Add schema:
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->decimal('price', 10, 2);
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

Run migration:
```bash
php artisan migrate
```

Generate module from migration:
```bash
php artisan make:module Product -a --from-migration=create_products_table --tests
```

**Expected:**
- ✅ DTO with inferred types
- ✅ Form requests with inferred validation
- ✅ Tests with sample data

**Verify:**
```bash
cat app/DTOs/ProductDTO.php
cat app/Http/Requests/StoreProductRequest.php
```

---

### Test 12: Inline Schema

```bash
php artisan make:module Order -a \
  --fields="order_number:string:unique, amount:decimal(10,2), status:enum:pending,processing,completed, notes:text" \
  --tests
```

**Expected:**
- ✅ DTO with exact fields
- ✅ Validation rules matching fields
- ✅ Enum handling

**Verify:**
```bash
cat app/DTOs/OrderDTO.php
```

---

## Action Layer Testing

### Test 13: Generate with Actions

```bash
php artisan make:module Invoice -a --actions
```

**Expected:**
- ✅ `app/Actions/Invoice/` directory
- ✅ `ListInvoices.php`
- ✅ `ShowInvoice.php`
- ✅ `CreateInvoice.php`
- ✅ `UpdateInvoice.php`
- ✅ `DeleteInvoice.php`

**Verify:**
```bash
ls -la app/Actions/Invoice/
```

---

## Feature Testing

### Test 14: Run Generated Tests

```bash
php artisan test tests/Feature/PostTest.php
```

**Expected:**
- ✅ All tests pass (or show clear failures)
- ✅ CRUD operations tested
- ✅ Validation tested

```bash
php artisan test --filter=PostTest
```

---

## Advanced Testing

### Test 15: Custom Output Directory

```bash
# Generate to custom location
php artisan make:swagger --output=resources/swagger --force
```

**Expected:**
- ✅ Files created in `resources/swagger/`

**Verify:**
```bash
ls -la resources/swagger/
```

---

### Test 16: Controller-Specific Generation

```bash
# Generate only for specific controller
php artisan make:swagger --controller=Post
```

**Expected:**
- ✅ Only `PostDoc.php` regenerated
- ✅ Other files unchanged

---

### Test 17: Path-Specific Generation

```bash
# Generate only for API routes
php artisan make:swagger --path=api
```

**Expected:**
- ✅ Only API controllers documented

---

## Integration Testing

### Test 18: With Optional Swagger-PHP

```bash
# Install swagger-php
composer require zircote/swagger-php --dev

# Generate docs
php artisan make:swagger --force

# Process with swagger-php
./vendor/bin/openapi --output public/docs/api.json app/Docs/

# Check output
cat public/docs/api.json | jq '.info'
```

**Expected:**
- ✅ Valid JSON spec generated
- ✅ Can be read by JSON validators

---

### Test 19: With L5-Swagger

```bash
# Install L5-Swagger
composer require darkaonline/l5-swagger

# Publish config
php artisan vendor:publish --provider="L5\\Swagger\\SwaggerServiceProvider"

# Generate docs
php artisan make:swagger --force

# Generate with L5
php artisan l5-swagger:generate

# Check routes
php artisan route:list | grep docs
```

**Expected:**
- ✅ Routes registered
- ✅ Storage files created
- ✅ UI accessible at /docs

---

## Error Handling

### Test 20: Error Cases

#### Case 1: Missing Module

```bash
php artisan make:module
```

**Expected:**
- ✅ Helpful error message
- ✅ Shows required arguments

#### Case 2: Duplicate Module

```bash
php artisan make:module Product
php artisan make:module Product  # Run again
```

**Expected:**
- ✅ Error about existing files
- ✅ Suggests `--force` flag

#### Case 3: Force Overwrite

```bash
php artisan make:module Product --force
```

**Expected:**
- ✅ Files overwritten
- ✅ Success message

---

## Performance Testing

### Test 21: Large Project

```bash
# Create many modules
for i in {1..10}; do
  php artisan make:module "Module$i" --swagger
done

# Check total generation time
time php artisan make:swagger --force

# Check file count
find app/Docs -name "*.php" | wc -l
```

**Expected:**
- ✅ Reasonable generation time (< 5 seconds)
- ✅ All files created correctly

---

## Validation Testing

### Test 22: Syntax Validation

```bash
# Validate all generated files
for file in app/Docs/*.php app/DTOs/*.php app/Services/*.php; do
  php -l "$file"
done
```

**Expected:**
- ✅ All files valid
- ✅ No syntax errors

---

## Clean Up

```bash
# Remove test modules
rm -rf app/Docs app/Repositories app/Services app/DTOs app/Http/Controllers/Api app/Http/Requests app/Http/Resources app/Actions tests/Feature app/Providers/*ServiceProvider.php

# Remove swagger UI
rm -rf public/docs

# Reset .env
grep -v SWAGGER .env > .env.tmp && mv .env.tmp .env
```

---

## Test Checklist

Run all tests:

```bash
# ✅ Test 1: Simple module
php artisan make:module Test1 && [ -f app/Repositories/Eloquent/Test1Repository.php ] && echo "✅ PASS" || echo "❌ FAIL"

# ✅ Test 2: API with Swagger
php artisan make:module Test2 -a --swagger && [ -f app/Docs/Test2Doc.php ] && echo "✅ PASS" || echo "❌ FAIL"

# ✅ Test 3: Swagger generation
php artisan make:swagger --force && [ -d app/Docs ] && echo "✅ PASS" || echo "❌ FAIL"

# ✅ Test 4: Configuration
php artisan swagger:config --show | grep -q "Theme" && echo "✅ PASS" || echo "❌ FAIL"

# ✅ Test 5: UI initialization
php artisan swagger:init && [ -f public/docs/index.html ] && echo "✅ PASS" || echo "❌ FAIL"
```

---

## Troubleshooting During Testing

### Issue: "Command not found"

```bash
php artisan package:discover
php artisan config:clear
```

### Issue: "File already exists"

```bash
php artisan make:module Product --force
```

### Issue: "Syntax error in generated file"

```bash
php -l app/Docs/ProductDoc.php
php -l app/DTOs/ProductDTO.php
```

### Issue: "UI not loading"

```bash
php artisan swagger:init
php artisan swagger:ui
# Check: http://localhost:8000/docs
```

---

## Next Steps

After successful testing:

1. ✅ Customize generated files to your needs
2. ✅ Run feature tests: `php artisan test`
3. ✅ Deploy to production
4. ✅ Share feedback: GitHub Issues

---

## Success Indicators

✅ All commands run without errors
✅ Files generate with correct syntax
✅ Swagger UI displays correctly
✅ Configuration works as expected
✅ Tests pass
✅ No PHP syntax errors

---

**Happy Testing!** 🚀
