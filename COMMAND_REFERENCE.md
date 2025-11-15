# 📚 Complete Command Reference

## Swagger Commands

### `php artisan make:swagger`

Generate PHPDoc documentation for all routes in your application.

**Signature:**
```bash
php artisan make:swagger [options]
```

**Options:**

| Option | Description |
|--------|-------------|
| `--path=PATH` | Scan specific routes path (e.g., `api`, `web`) |
| `--controller=NAME` | Generate only for specific controller |
| `--output=PATH` | Custom output directory (default: `app/Docs`) |
| `--force` | Overwrite existing files |

**Examples:**

```bash
# Generate for all routes
php artisan make:swagger

# Generate only for API routes
php artisan make:swagger --path=api

# Generate only for specific controller
php artisan make:swagger --controller=Product

# Custom output directory
php artisan make:swagger --output=resources/docs

# Force overwrite
php artisan make:swagger --force
```

---

### `php artisan make:module MODEL [options]`

Generate complete module with optional Swagger documentation.

**Key Options for Swagger:**

```bash
# Generate module with Swagger docs
php artisan make:module Product --swagger

# Only Swagger documentation (no module files)
php artisan make:module Product --swagger --no-controller --no-dto --no-service

# Full stack with Swagger
php artisan make:module Product -a --swagger --tests
```

**Generated File:** `app/Docs/ProductDoc.php`

---

### `php artisan swagger:config`

Interactive configuration for Swagger UI.

**Signature:**
```bash
php artisan swagger:config [options]
```

**Options:**

| Option | Description |
|--------|-------------|
| `--show` | Display current configuration |
| `--reset` | Reset to defaults |
| `--export-env` | Export to `.env` file |

**Usage:**

```bash
# Interactive configuration menu
php artisan swagger:config

# Show current settings
php artisan swagger:config --show

# Export to .env
php artisan swagger:config --export-env

# Reset to defaults
php artisan swagger:config --reset
```

---

### `php artisan swagger:init`

Initialize Swagger UI files based on configuration.

**Signature:**
```bash
php artisan swagger:init
```

**Creates:**
- `public/docs/` - Swagger UI files
- `public/docs/theme/` - Theme-specific CSS/JS
- Configuration based on `config/module-generator.php`

---

### `php artisan swagger:generate`

Generate OpenAPI JSON specification from routes (for JSON UI).

**Signature:**
```bash
php artisan swagger:generate [options]
```

**Options:**

| Option | Description |
|--------|-------------|
| `--format=FORMAT` | Output format (json, yaml) |
| `--output=PATH` | Output file path |

**Creates:**
- `storage/app/swagger.json` - OpenAPI specification
- Used by UI to display endpoints

---

### `php artisan swagger:ui`

Start development server for Swagger UI.

**Signature:**
```bash
php artisan swagger:ui [port]
```

**Default:** `http://localhost:8000/docs`

**Usage:**

```bash
# Default port 8000
php artisan swagger:ui

# Custom port
php artisan swagger:ui 9000
```

---

## Module Generation Commands

### `php artisan make:module`

**Full Signature:**
```bash
php artisan make:module {name} {options}
```

**Common Combinations:**

```bash
# Basic module
php artisan make:module Product

# API module with everything
php artisan make:module Product -a

# Full stack with swagger
php artisan make:module Product -a --swagger --tests --force

# Web module
php artisan make:module BlogPost --controller=Web

# With action layer
php artisan make:module Order --actions --requests --tests

# From migration
php artisan make:module Product --from-migration=create_products_table -a

# With inline schema
php artisan make:module Product --fields="name:string, price:decimal(10,2)" -a
```

**Options Reference:**

| Option | Alias | Description |
|--------|-------|-------------|
| `--api` | `-a` | API module (controller, requests, resources) |
| `--actions` | | Generate action classes |
| `--requests` | `-r` | Generate form requests |
| `--tests` | `-t` | Generate feature tests |
| `--swagger` | `-sg` | Generate Swagger docs |
| `--controller=` | `-c` | Custom controller path |
| `--from-migration=` | | Infer from migration |
| `--fields=` | | Inline schema |
| `--no-controller` | `-nc` | Skip controller |
| `--no-resource` | `-nr` | Skip API resource |
| `--no-dto` | `-nd` | Skip DTO |
| `--no-test` | `-nt` | Skip tests |
| `--no-provider` | `-np` | Skip provider |
| `--no-swagger` | | Skip Swagger docs |
| `--force` | `-f` | Overwrite files |

---

## Configuration Commands

### Check Configuration

```bash
# View current config
php artisan config:show module-generator

# View Swagger config
php artisan swagger:config --show
```

### Environment Variables

```bash
# Create .env entries
SWAGGER_THEME=tailwind
SWAGGER_DARK_MODE=true
SWAGGER_COLORS_PRIMARY=#3b82f6
```

---

## Workflow Examples

### Example 1: Complete API Documentation

```bash
# 1. Create module with Swagger
php artisan make:module Product -a --swagger --tests

# 2. Initialize Swagger UI
php artisan swagger:init

# 3. Generate JSON spec
php artisan swagger:generate

# 4. View in browser
php artisan swagger:ui
# Visit: http://localhost:8000/docs
```

### Example 2: Documentation Only

```bash
# Generate docs without module files
php artisan make:module Payment \
  --swagger \
  --no-controller \
  --no-dto \
  --no-service \
  --force

# Result: app/Docs/PaymentDoc.php
```

### Example 3: Update Existing Documentation

```bash
# Regenerate docs for all routes
php artisan make:swagger --force

# Configure UI theme
php artisan swagger:config

# Reinitialize UI
php artisan swagger:init
```

### Example 4: Custom Schema

```bash
# Module with inline fields
php artisan make:module Product \
  -a \
  --swagger \
  --fields="name:string|required, price:decimal(10,2)|required, status:enum:draft,published|required" \
  --tests

# From migration
php artisan make:module Product \
  -a \
  --swagger \
  --from-migration=create_products_table \
  --tests
```

---

## Troubleshooting Commands

### Verify Installation

```bash
# Check if commands are available
php artisan list | grep swagger
php artisan list | grep make:module

# Test basic functionality
php artisan make:module Test --no-controller --no-service --no-dto --force
ls -la app/Docs/TestDoc.php
```

### Clear Caches

```bash
php artisan config:cache
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

### Reset Swagger Configuration

```bash
php artisan swagger:config --reset
php artisan swagger:init --force
rm -rf public/docs
php artisan swagger:init
```

### Check PHP Syntax

```bash
# Verify generated files
php -l app/Docs/ProductDoc.php

# List all generated docs
find app/Docs -name "*.php" -exec php -l {} \;
```

---

## Integration Commands

### With L5-Swagger (Optional)

```bash
# Install L5-Swagger (optional)
composer require darkaonline/l5-swagger

# Generate documentation with make:swagger
php artisan make:swagger --force

# Generate Swagger UI via L5
php artisan l5-swagger:generate

# View at /docs
```

### With Swagger-PHP Directly (Optional)

```bash
# Install swagger-php (optional)
composer require zircote/swagger-php

# Generate using swagger-php binary
./vendor/bin/openapi --output docs/api.json app/Docs/

# Use generated spec elsewhere
```

---

## Next Steps

- ✅ Run `php artisan make:module Product --swagger` to generate docs
- ✅ Run `php artisan swagger:init` to initialize UI
- ✅ Run `php artisan swagger:ui` to view docs
- ✅ Customize configuration with `php artisan swagger:config`
- ⭕ (Optional) Install `zircote/swagger-php` for advanced features
- ⭕ (Optional) Install `darkaonline/l5-swagger` for full integration

---

**📖 Related Documentation:**
- [Swagger No Dependencies](SWAGGER_NO_DEPENDENCIES.md)
- [PHPDoc Generation](SWAGGER_PHPDOC_GENERATION.md)
- [Swagger Configuration](SWAGGER_CONFIG.md)
- [Quick Start](SWAGGER_QUICKSTART.md)
