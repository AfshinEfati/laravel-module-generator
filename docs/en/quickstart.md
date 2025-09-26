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
php artisan vendor:publish --tag=module-generator
php artisan vendor:publish --tag=module-generator-stubs --force
```

The first command publishes the configuration file and helper classes. The second copies the default stubs into `resources/stubs/module-generator` so you can customise controllers, requests, or tests. Re-run the command whenever you pull upstream changes to keep local stubs aligned.

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
