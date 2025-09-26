---
title: Laravel Module Generator
hide:
  - navigation
---

[🇮🇷 فارسی](/fa/){ .language-switcher }

<div class="hero">
  <div class="hero__content">
    <h1 class="hero__title">Build cohesive Laravel modules in minutes</h1>
    <p class="hero__lead">Scaffold DTOs, form requests, resources, tests, and providers from a single schema so every module ships with production-ready defaults and identical structure.</p>
    <div class="hero__actions">
      <a class="md-button md-button--primary" href="quickstart/">Start from the terminal</a>
      <a class="md-button md-button--secondary" href="installation/">Install the package</a>
      <a class="md-button" href="https://github.com/efati/laravel-module-generator" target="_blank" rel="noopener">Star on GitHub</a>
    </div>
  </div>
</div>

<div class="badge-row">
  <a href="https://packagist.org/packages/efati/laravel-module-generator" target="_blank" rel="noopener">
    <img alt="Packagist version" src="https://img.shields.io/packagist/v/efati/laravel-module-generator.svg?label=packagist&color=4c51bf">
  </a>
  <a href="https://packagist.org/packages/efati/laravel-module-generator" target="_blank" rel="noopener">
    <img alt="Packagist downloads" src="https://img.shields.io/packagist/dt/efati/laravel-module-generator.svg?color=10b981">
  </a>
  <a href="https://github.com/efati/laravel-module-generator" target="_blank" rel="noopener">
    <img alt="GitHub stars" src="https://img.shields.io/github/stars/efati/laravel-module-generator.svg?style=flat&color=0ea5e9">
  </a>
  <a href="https://github.com/efati/laravel-module-generator/actions/workflows/docs.yml" target="_blank" rel="noopener">
    <img alt="Docs build" src="https://img.shields.io/github/actions/workflow/status/efati/laravel-module-generator/docs.yml?branch=main&label=docs">
  </a>
</div>

## Why teams choose the generator

<div class="feature-grid">
  <div class="feature-card">
    <h3>:octicons-rows-16: Schema-driven scaffolding</h3>
    <p>Parse migrations or inline field definitions once and hydrate DTOs, validation rules, API resources, factories, and feature tests without duplicating metadata.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-rocket-16: Production-ready defaults</h3>
    <p>Generated controllers ship with response helpers, pagination support, and localisation-ready strings so you can deploy without polishing boilerplate.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-code-square-16: Extensible stubs</h3>
    <p>Publish the stubs once, then customise namespaces, traits, or logging to meet your internal conventions while staying compatible with package updates.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-circuit-board-16: Container-aware providers</h3>
    <p>Service providers register repositories, services, observers, and policies automatically so your dependency graph stays aligned with every new module.</p>
  </div>
</div>

<div class="quickstart">
  <h2>Quick start from the CLI</h2>
  <p>Pick the recipe that matches your project and scaffold a full module with tests, resources, and API endpoints.</p>

=== "Inline schema"

    ```bash
    composer require efati/laravel-module-generator
    php artisan make:module Product \
      --api --requests --tests \
      --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
    ```

=== "Use an existing migration"

    ```bash
    composer require efati/laravel-module-generator
    php artisan vendor:publish --tag=module-generator
    php artisan make:module Product \
      --api --requests --tests \
      --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
    ```

</div>

## Learn the workflow

- Understand the [installation](installation.md) checklist and publish configurable assets.
- Follow the [quickstart guide](quickstart.md) to create your first feature-complete module.
- Browse [usage recipes](usage.md) for CRUD variations, DTO-only scenarios, and test strategies.
- Dive into [advanced guides](advanced.md) to override stubs, register hooks, and wire custom generators.

## Explore the generated structure

The reference section captures what is created on disk, how service providers are wired, and which CLI flags toggle each artifact.

- Review the [CLI & file reference](reference.md) for every option and output path.
- Track changes in the [changelog](changelog.md) and subscribe to release notes.
- Deploy the docs with the [GitHub Pages setup](github-pages-setup.md) workflow.

<div class="cta-banner">
  <h2>Ready to scaffold your next module?</h2>
  <p>Install the package, describe your schema once, and let the generator deliver a consistent, testable Laravel module in minutes.</p>
  <a class="md-button md-button--primary" href="installation/">Install &amp; configure</a>
</div>
