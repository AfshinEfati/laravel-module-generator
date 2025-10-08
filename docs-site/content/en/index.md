---
title: Laravel Module Generator
hide:
  - navigation
---

[🇮🇷 فارسی](../fa/index.md){ .language-switcher }

<div class="hero">
  <div class="hero__content">
    <span class="hero__eyebrow">Laravel Module Generator</span>
    <h1 class="hero__title">Scaffold Laravel modules in minutes</h1>
    <p class="hero__lead">Generate controllers, DTOs, services, repositories, requests, and tests from a single Artisan command. Tailor every layer with your stubs while the structure stays predictable.</p>
    <div class="hero__actions">
      <a class="md-button md-button--primary" href="installation/">Installation guide</a>
      <a class="md-button md-button--secondary" href="quickstart/">Run your first module</a>
      <a class="md-button" href="https://github.com/AfshinEfati/laravel-module-generator" target="_blank" rel="noopener">View on GitHub</a>

    </div>
  </div>
</div>

## Build modules that feel hand-crafted

- Describe your fields once and let the generator create DTOs, form requests, resources, factories, policies, and feature tests that agree on naming and validation.
- Keep controllers lean with ready-to-use response helpers, pagination, and API resource wiring that matches Laravel best practices.
- Publish the stubs to tailor namespaces, logging, or localisation while staying compatible with future updates.

## Quick start

<div class="landing-grid">
  <div class="landing-card" markdown="1">
    <h3>Install the package</h3>
    ```bash
    composer require efati/laravel-module-generator
    ```
    <p>After installation the service provider registers the <code>make:module</code> command automatically.</p>
  </div>
  <div class="landing-card" markdown="1">
    <h3>Generate a module</h3>
    ```bash
    php artisan make:module Product \
      --api --requests --tests \
      --fields="name:string, price:decimal(10,2), is_active:boolean"
    ```
    <p>The generator creates controllers, resources, DTOs, repositories, services, and feature tests in a single pass.</p>
  </div>
</div>

## What’s included

- Service providers register your repositories, services, policies, and observers.
- Configurable stubs give you full control over folder structure and base classes.
- Feature and unit tests are scaffolded with factories so you can verify behaviour immediately.
- Optional Swagger annotations (`--swagger`) live in dedicated `App\Docs\{Module}Doc` classes (default path `app/Docs`), keeping controllers clean while warning you when the swagger package is missing.

## Learn more

- Follow the [installation checklist](installation.md) to publish configuration and stubs.
- Use the [quickstart recipes](quickstart.md) for inline schemas or existing migrations.
- Dive into [usage patterns](usage.md), [advanced guides](advanced.md), and the [CLI reference](reference.md) when you need more control.
