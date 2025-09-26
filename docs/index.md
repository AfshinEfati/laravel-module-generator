# Laravel Module Generator Docs

Laravel Module Generator helps teams spin up fully wired modules—repositories, services, DTOs, controllers, API resources, form requests, and feature tests—with a single Artisan command. Version **6.2.4** brings smarter validation, richer schema introspection, and polished resource output so your scaffolding stays production-ready.

## Why use the generator?

- **One command, full stack** – `php artisan make:module` wires repositories, services, provider bindings, DTO pipelines, REST controllers, API resources, form requests, and CRUD feature tests.
- **Schema-aware scaffolding** – Feed the command a migration hint or an inline `--fields` definition and it will derive fillable attributes, casts, validation rules, and even nested resource metadata before your model class exists.
- **Production-friendly defaults** – Update Form Requests automatically ignore the current record for unique checks, resources format boolean/timestamp responses consistently, and tests reuse your `.env` database configuration.

## Quick start checklist

1. [Install the package](installation.md) via Composer and publish the configuration/stubs you want to customise.
2. Adjust namespaces and target folders inside `config/module-generator.php` so generated classes drop into the right modules.
3. Run your first scaffold:

   ```bash
   php artisan make:module Product --api --requests --controller=Admin --tests
   ```

   The command produces a controller under `Http/Controllers/Api/V1/Admin`, store/update Form Requests with migration-informed validation, API resources, DTOs, providers, and feature tests ready to run.

4. Explore [advanced tooling](advanced.md) such as the Jalali `goli()` helper, feature test scaffolding tips, and strategies for overriding stubs.

## Release highlights

- **v6.2.4** – Form Requests convert `unique:` pipe rules into `Rule::unique()->ignore()` during updates, keeping validation stable with route-model binding. Resource output continues to normalise booleans/dates via the status helper utilities.
- **v6.1.0** – Resource generation inspects migrations and runtime relations to eager-load nested resources automatically, while inline `--fields` metadata drives casts/fillable arrays for downstream generators.
- **v6.0.0** – Migration introspection captures nullable, enum, unique, and foreign key metadata so DTOs, requests, resources, and tests stay aligned.

Check the [full changelog](changelog.md) for a chronological breakdown of 6.x updates and earlier releases.

## Need more?

- [Usage reference](usage.md) – CLI flags, aliases, and real-world scenarios.
- [Advanced features](advanced.md) – Feature test walkthroughs and the Jalali date helper.
- [GitHub Pages workflow](github-pages-setup.md) – Publish these docs with GitHub Pages.

Found a gap? Open an issue or submit a pull request—community contributions keep the generator sharp.

