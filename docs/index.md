# Laravel Module Generator

Build Laravel modules in minutes with opinionated scaffolding, expressive commands, and a developer-friendly workflow.

[Get started with Installation →](./installation.md)

> ### ✅ Compatible with Laravel 10 & 11
> Enjoy first-class support for the latest long-term support releases out of the box.

> ### ⚙️ Built-in `goli()` helper
> Access powerful helper functions generated alongside every module for cohesive tooling.

> ### 🧩 Works with your existing stack
> Integrates seamlessly with Blade, Livewire, Inertia, and REST controllers.

## Why developers love it

<div class="feature-grid">

- 🎯 **Command-driven workflow** — Generate modules, resources, and tests with a single artisan command.
- 🧱 **Consistent structure** — Opinionated scaffolding keeps every module aligned across projects.
- 🧪 **Ready-to-run tests** — Stubbed PHPUnit suites ensure every module ships with coverage.
- 🔌 **Extensible templates** — Swap or extend stubs to fit your organization's conventions.
- 📦 **Configurable publishing** — Publish configs, translations, and assets tailored to each module.

</div>

## What you get

When you run `php artisan module:make Blog`, the generator scaffolds a production-ready module directory:

```text
modules/
└── Blog/
    ├── Providers/
    │   └── BlogServiceProvider.php
    ├── Http/
    │   ├── Controllers/
    │   │   └── BlogController.php
    │   └── Requests/
    │       └── StorePostRequest.php
    ├── Models/
    │   └── Post.php
    ├── Database/
    │   ├── factories/
    │   │   └── PostFactory.php
    │   └── seeders/
    │       └── BlogSeeder.php
    ├── routes/
    │   └── web.php
    ├── resources/
    │   ├── views/
    │   │   └── index.blade.php
    │   └── lang/
    │       └── en/messages.php
    ├── Tests/
    │   └── Feature/
    │       └── BlogTest.php
    └── module.json
```

## Next steps

1. Install the package following the [Installation guide](./installation.md).
2. Configure the generator for your project in `config/module-generator.php`.
3. Run your first module command and customize the scaffolding to match your domain.

