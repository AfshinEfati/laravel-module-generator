# Quickstart

[🇮🇷 فارسی](../fa/quickstart.md){ .language-switcher }

Follow this checklist to scaffold your first module in less than five minutes.

## 1. Install the package

```bash
composer require efati/laravel-module-generator
```

MkDocs build automation is already configured in the repository, so installing the dependency locally is the only prerequisite.

## 2. Publish configuration and stubs (optional)

```bash
php artisan vendor:publish
```

From the provider list pick `Efati\ModuleGenerator\ModuleGeneratorServiceProvider`, then choose the `module-generator` tag to publish the configuration and helper classes. Run the command again, select the same provider, and choose the `module-generator-stubs` tag (add `--force` if you need to overwrite existing templates). The exported stubs in `resources/stubs/module-generator` let you customise controllers, requests, or tests and can be refreshed after pulling upstream changes.

## 3. Describe the schema once

Pick one of the supported strategies:

- **Inline fields** — pass a comma-separated list with name, type, and modifiers (e.g. `price:decimal(10,2):nullable`).
- **Existing migration** — point the generator to a migration path and reuse the column metadata that already exists in your project.

## 4. Run the generator

```bash
php artisan make:module Product \
  --api --requests --tests \
  --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
```

The command creates:

- Controller, request classes, DTO, resource, service, repository, and service provider.
- Feature test suite with happy-path and validation scenarios.
- Route registration entries based on the flags you pass.

## 5. Verify and commit

1. Run `phpunit` to execute the generated feature tests.
2. Tailor the published stubs or generated classes if your domain requires additional logic.
3. Commit both the generated files and the configuration/stubs so future teammates inherit the same setup.

## Next steps

- Review the [usage guide](usage.md) for flag combinations and recipes.
- Explore [advanced customisation](advanced.md) to add hooks or entirely new generators.
- Scan the [CLI reference](reference.md) when you need an at-a-glance list of options.

