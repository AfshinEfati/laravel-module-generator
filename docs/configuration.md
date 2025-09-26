# Configuration

After installing the package you can tailor the generator to match your project’s folder structure, namespaces, and default behaviours.

## Publish the assets

```bash
php artisan vendor:publish --tag=module-generator
```

The command copies:

- `config/module-generator.php` – stores paths, namespaces, and default feature toggles.
- Base repository/service classes and the `StatusHelper`, providing a starting point for generated modules.

Publish the stubs if you want to override templates:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

All stubs live in `resources/stubs/module-generator` and are read on every generation run.

## Adjust namespaces and paths

`config/module-generator.php` lets you point each artefact to custom directories:

```php
'paths' => [
    'repository' => [
        'eloquent'  => 'Domains/Inventory/Repositories',
        'contracts' => 'Domains/Inventory/Contracts',
    ],
    'service' => [
        'concretes' => 'Domains/Inventory/Services',
        'contracts' => 'Domains/Inventory/Services/Contracts',
    ],
    'dto'          => 'Domains/Inventory/DTOs',
    'provider'     => 'Domains/Inventory/Providers',
    'controller'   => 'App/Http/Controllers/Inventory',
    'resource'     => 'App/Http/Resources/Inventory',
    'form_request' => 'App/Http/Requests/Inventory',
],
```

Paths are relative to `app/` (tests use project-root paths). Update them once and every subsequent `make:module` call honours the new structure.

## Toggle default behaviours

The `defaults` section controls which artefacts are generated when you omit CLI flags. For example, enabling form requests globally only takes one change:

```php
'defaults' => [
    'with_controller'    => true,
    'with_form_requests' => true,
    'with_unit_test'     => true,
    'with_resource'      => true,
    'with_dto'           => true,
    'with_provider'      => true,
],
```

These defaults are merged with the command options at runtime, so you can still use `--no-controller` or `--tests` to override them for individual modules.

## Provider registration

Provider generation automatically appends the new service provider to `bootstrap/providers.php` (Laravel 11) or `config/app.php` (Laravel 10 and below). If you disable providers with `--no-provider`, remember to bind the repository and service manually in your own provider or container bindings.

## Stub customisation tips

- Add traits, interfaces, or docblocks that your team expects in repositories and services.
- Update the API controller stub to match your preferred response envelope or status codes.
- Introduce localisation hooks, logging, or domain-specific contracts directly inside the stub.

Whenever you edit a stub, rerun the generator with `--force` to regenerate existing modules with the updated template.
