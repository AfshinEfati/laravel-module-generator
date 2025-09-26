# Advanced guides

[🇮🇷 فارسی](../fa/advanced.md){ .language-switcher }


Customise the generator beyond the defaults to match your organisation.

## Override stubs per artefact

1. Publish the stubs: `php artisan vendor:publish --tag=module-generator-stubs`.
2. Edit the templates in `resources/stubs/module-generator`.
3. Re-run the generator with `--force` to apply your changes.

Each stub receives metadata about fields, relationships, and namespace. You can include additional traits, change method signatures, or add docblocks that reference your conventions.

## Hook into lifecycle events

The generator dispatches events you can listen to for further automation:

- `ModuleGenerated` – fire notifications, enqueue jobs, or run additional artisan commands.
- `ModuleFileCreated` – inspect generated files and apply linting or formatting automatically.

Register listeners in your application service provider to extend the workflow.

## Build custom generators

If your organisation needs bespoke artefacts (e.g. GraphQL resolvers or front-end adapters), follow this approach:

1. Extend the base generator classes shipped with the package.
2. Register your generator in the published configuration under `custom_generators`.
3. Reference the generator in your stubs or command options.

This keeps the core package intact while letting you layer additional behaviour on top of the provided pipeline.

## Integrate with CI/CD

- Ensure `composer install --no-dev` runs before invoking `php artisan make:module` inside automated scripts.
- Use the generated feature tests in your CI pipeline for confidence when regenerating modules.
- Leverage the existing [GitHub Pages workflow](github-pages-setup.md) to publish documentation updates alongside code changes.

