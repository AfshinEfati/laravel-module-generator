# Installation

Get up and running with the Laravel Module Generator in just a few commands. Start by pulling in the package via Composer and then publish the configuration so you can tailor the generator to your application's structure.

## Require the package

```bash
composer require efati/laravel-module-generator
```

## Publish the configuration

```bash
php artisan vendor:publish --tag=module-generator-config
```

Publishing the configuration adds `config/module-generator.php` to your project. This file controls the namespace and path defaults for every artefact the generator can create.

## Configure namespaces and paths

Inside `config/module-generator.php` you can point each component—models, repositories, services, controllers, DTOs, tests, and more—to the folders and namespaces that match your project's architecture. Update the values in the `namespaces` and `paths` sections so newly generated classes land exactly where you expect them.

## Customise generator stubs

If you need the generated files to follow team-specific conventions, publish the stubs and edit them to match your preferred structure:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

The command copies every stub to `resources/stubs/module-generator`, where you can update imports, docblocks, method signatures, or any other scaffolding details. The generator will read from your customised stubs on subsequent runs, so your adjustments take effect immediately.

---

[← Back to Configuration](../README.md#configuration)
