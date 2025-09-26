# Configuration

[🇮🇷 فارسی](/fa/configuration/){ .language-switcher }

Fine-tune the generator once so every teammate creates modules with the same structure.

## Global options

The published `config/module-generator.php` file exposes the defaults that control namespace, output paths, and enabled generators. Review these sections after installation:

- **`namespace`** – Sets the root namespace for generated classes. Align this with your domain layer (e.g. `App\Modules`).
- **`paths`** – Controls where controllers, repositories, resources, and tests are stored. Map these to your preferred directory structure.
- **`defaults`** – Toggles for enabling DTOs, resources, API controllers, tests, and policies globally. Command-line flags can override the defaults per module.

## Stubs

If you publish the stubs, the generator reads templates from `resources/stubs/module-generator`. Each stub matches a specific artefact and can include Blade variables:

- `controller.api.stub`, `controller.web.stub`
- `dto.stub`, `resource.stub`, `repository.stub`, `service.stub`
- `request.store.stub`, `request.update.stub`
- `tests.feature.stub`

Use the same placeholders as the package stubs. When upstream updates introduce new placeholders, re-publish to diff and merge changes.

## Registering modules automatically

Generated service providers register repositories and services in the container. Ensure the provider path configured in `config/module-generator.php` matches one of the auto-discovery locations:

- Laravel 10 – `config/app.php`
- Laravel 11 – `bootstrap/providers.php`

The generator appends the new provider entry automatically when the file exists.

## Environment variables

A few behaviours can be adjusted via environment variables:

- `MODULE_GENERATOR_FORCE_OVERWRITE=true` – treat existing files as overwritable without using the `--force` flag.
- `MODULE_GENERATOR_DISABLE_TESTS=true` – skip generating feature tests globally.

Use the flags sparingly and prefer checking them into `.env.example` to document the behaviour for new contributors.
