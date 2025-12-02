# Migration Guide - Upgrading to Latest Version

This guide helps you upgrade between different versions of Laravel Module Generator.

## Upgrading from v6.x to v7.x

### Breaking Changes

1. **Namespace Changes**

   - Repository contracts moved to separate namespace
   - Service contracts now use consistent naming

2. **Configuration Structure**

   - Path configuration now supports nested arrays
   - Middleware configuration moved to `defaults` section

3. **Generated File Locations**
   - Repository files now in `Repositories/Eloquent` by default
   - Services in `Services` directory
   - Actions in `Actions/{ModuleName}` structure

### Migration Steps

#### Step 1: Update Configuration

Old config/module-generator.php:

```php
'paths' => [
    'repository' => 'Repositories',
    'service' => 'Services',
],
```

New structure:

```php
'paths' => [
    'repository' => [
        'contracts' => 'Repositories/Contracts',
        'eloquent' => 'Repositories/Eloquent',
    ],
    'service' => [
        'contracts' => 'Services/Contracts',
        'concretes' => 'Services',
    ],
],
```

#### Step 2: Republish Assets

```bash
php artisan vendor:publish \
  --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" \
  --tag=module-generator --force
```

#### Step 3: Update Existing Modules (Optional)

You can regenerate existing modules with the new structure:

```bash
# Backup old files first
cp -r app/Services app/Services.backup
cp -r app/Repositories app/Repositories.backup

# Regenerate with --force
php artisan make:module Product -a --force
```

#### Step 4: Update Service Provider Registrations

If you manually registered service providers, update the paths:

Old:

```php
// config/app.php
'providers' => [
    App\Providers\ProductServiceProvider::class,
],
```

New (Laravel 11):

```php
// bootstrap/providers.php
return [
    App\Providers\ProductServiceProvider::class,
];
```

## Upgrading from v5.x to v6.x

### Changes

1. **DTOs now support validation**

   - Added `fromRequest()` helper method
   - Added `toArray()` method

2. **Services support dynamic queries**

   - New `findDynamic()` method with chainable parameters
   - Better relationship handling

3. **Actions are now optional**
   - Use `--actions` or `--no-actions` to control
   - API modules generate actions by default

### Migration Steps

#### Step 1: Update DTO Usage

Old approach:

```php
$data = $request->validated();
$product = $this->service->store($data);
```

New approach (recommended):

```php
$dto = ProductDTO::fromRequest($request);
$product = $this->service->store($dto);
```

#### Step 2: Update Service Usage

Old approach:

```php
$products = $this->repository->all();
```

New approach:

```php
$products = $this->service->findDynamic(
    where: ['is_active' => true],
    with: ['category'],
    orderBy: 'name'
);
```

## Upgrading from v4.x to v5.x

### Major Changes

1. **Repository Pattern Enhanced**

   - Interfaces are now required
   - Auto-binding in service provider

2. **Tests are More Comprehensive**

   - Feature tests now include validation tests
   - Better field-aware test data generation

3. **Configuration System**
   - New `config/module-generator.php` replaces inline config
   - Centralized default settings

### Migration Steps

#### Step 1: Create Config File

```bash
php artisan vendor:publish \
  --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" \
  --tag=module-generator
```

#### Step 2: Update Existing Service Providers

Ensure your service providers use constructor dependency injection:

```php
// New style
public function register(): void
{
    $this->app->bind(
        ProductRepositoryInterface::class,
        ProductRepository::class
    );
}
```

#### Step 3: Update Model Bindings

If using route model binding, update the container binding:

```php
// In AppServiceProvider or ModuleServiceProvider
$this->app->bind('product', ProductRepository::class);
```

## Upgrading from v3.x to v4.x

### Key Changes

1. **DTO Classes Introduced**

   - Type-safe payload handling
   - Request conversion helpers

2. **API Resources**

   - Consistent JSON formatting
   - Relationship loading in resources

3. **Middleware Support**
   - Controllers now support middleware configuration
   - Auth middleware integration

### Migration Steps

#### Step 1: Update Controllers

Ensure your controllers use dependency injection:

```php
// Updated controller structure
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service
    ) {}

    public function store(StoreProductRequest $request)
    {
        $product = $this->service->store($request->validated());
        return new ProductResource($product);
    }
}
```

#### Step 2: Add Resources to Routes

Update your API route registrations:

```php
// routes/api.php
Route::apiResource('products', ProductController::class);
```

#### Step 3: Middleware Configuration

Add authentication to your routes:

```php
Route::middleware('auth:sanctum')
    ->apiResource('products', ProductController::class);
```

## Upgrading from v2.x to v3.x

### Breaking Changes

1. **Namespace Structure**

   - Services moved to `App\Services`
   - Repositories moved to `App\Repositories`

2. **Base Classes**

   - `BaseService` now abstract
   - `BaseRepository` signature changed

3. **Form Requests**
   - Organized under `Http\Requests\{Module}`
   - Automatic validation rule generation

### Migration Steps

#### Step 1: Update Namespaces

```bash
# Find and replace in all files
# OLD: App\Services\* => NEW: App\Services\*
# OLD: App\Repositories\* => NEW: App\Repositories\*

find app -type f -name "*.php" -exec sed -i \
  's/App\\Services\\Contracts/App\\Services\\Contracts/g' {} \;
```

#### Step 2: Update Service Provider

Update your service provider registrations:

```php
// In any ServiceProvider
$this->app->bind(
    'App\\Services\\Contracts\\ProductServiceInterface',
    'App\\Services\\ProductService'
);
```

#### Step 3: Update Controller Imports

```php
// Update imports to new namespaces
use App\Services\ProductService;
use App\Repositories\ProductRepository;
```

## General Tips for All Upgrades

### 1. Backup Before Upgrading

```bash
git add .
git commit -m "Backup before module generator upgrade"
```

### 2. Test After Upgrade

```bash
php artisan test
php artisan tinker  # Manual testing
```

### 3. Check Generated Files

After regenerating modules, verify:

- [ ] Controller methods are correct
- [ ] Service bindings work
- [ ] Tests pass
- [ ] API endpoints respond

### 4. Update Documentation

- [ ] Update API documentation
- [ ] Update internal docs
- [ ] Update team wiki

### 5. Gradual Migration

You don't need to regenerate everything at once:

```bash
# Regenerate modules one by one
php artisan make:module Product -a --force
php artisan make:module Order -a --force
php artisan make:module Category -a --force
```

### 6. Fallback Strategy

If issues arise, keep old files:

```bash
# Create backup directory
mkdir -p backups
cp -r app/Services backups/Services.v6
cp -r app/Repositories backups/Repositories.v6

# Then regenerate
php artisan make:module * -a --force
```

## Troubleshooting Upgrades

### Issue: "Class not found"

**Cause:** Namespace mismatch after upgrade

**Solution:**

```bash
# Regenerate with correct namespaces
php artisan make:module Product -a --force

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Issue: "Method not found in service"

**Cause:** Old service interface used

**Solution:**

```bash
# Regenerate service and provider
php artisan make:module Product --no-controller --force
```

### Issue: Tests failing after upgrade

**Cause:** Test structure or assertions changed

**Solution:**

```bash
# Regenerate tests
php artisan make:module Product --tests --force

# Run tests
php artisan test
```

### Issue: Provider not registering

**Cause:** Provider registration path changed

**Solution:**

1. Check bootstrap/providers.php (Laravel 11)
2. Check config/app.php (Laravel 10)
3. Regenerate provider: `php artisan make:module Product --no-provider`

## Version History

- **v7.x** - Enhanced error handling, better configuration
- **v6.x** - Restructured paths, service improvements
- **v5.x** - DTO validation, dynamic queries, optional actions
- **v4.x** - DTOs, API Resources, comprehensive tests
- **v3.x** - Namespace refactor, base classes
- **v2.x** - Initial repository/service pattern

## Support

If you encounter issues during upgrade:

1. Check [CHANGELOG](CHABELOG.md)
2. Review [API Reference](API_REFERENCE.md)
3. See [Examples](EXAMPLES.md)
4. Open an issue on [GitHub](https://github.com/AfshinEfati/Laravel-Scaffolder/issues)
