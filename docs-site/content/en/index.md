---
title: Laravel Module Generator
hide:
  - navigation
---

[🇮🇷 فارسی](../fa/index.md){ .language-switcher }

<section class="hero" markdown="1">
  <div class="hero__content" markdown="1">
    <p class="hero__eyebrow">Laravel Module Generator</p>
    <h1 class="hero__title">Scaffold Laravel modules in minutes</h1>
    <p class="hero__lead">
      Generate controllers, DTOs, services, repositories, requests, tests, and documentation from a single Artisan command.
      Ship production-ready modules that follow best practices and keep your project structure predictable.
    </p>
    <div class="hero__actions">
      [Get started](installation.md){ .md-button .md-button--primary }
      [View on GitHub](https://github.com/AfshinEfati/laravel-module-generator){ .md-button .md-button--secondary target=_blank }
    </div>
  </div>
</section>

## What you get out of the box

- Describe your fields once and the generator creates DTOs, form requests, resources, factories, policies, and tests that agree on naming and validation.
- Keep controllers lean with ready-to-use response helpers, pagination, and API resource wiring that matches Laravel conventions.
- Publish the stubs to tailor namespaces, logging, localisation, or documentation while staying compatible with future updates.

## Quick start

<div class="landing-grid">
  <div class="landing-card" markdown="1">
    <h3>Install the package</h3>
    ```bash
    composer require efati/laravel-module-generator
    ```
    <p>The service provider registers the <code>make:module</code> command automatically after installation.</p>
  </div>
  <div class="landing-card" markdown="1">
    <h3>Generate a module</h3>
    ```bash
    php artisan make:module Product \
      --api --requests --tests \
      --fields="name:string, price:decimal(10,2), is_active:boolean"
    ```
    <p>Controllers, resources, DTOs, repositories, services, and feature tests are scaffolded in a single pass.</p>
  </div>
</div>

## Keep exploring

- Follow the [installation checklist](installation.md) to publish configuration, factories, and stubs.
- Use the [quickstart recipes](quickstart.md) for inline schemas or existing migrations.
- Dive into [usage patterns](usage.md), [advanced guides](advanced.md), and the [CLI reference](reference.md) whenever you need more control.
