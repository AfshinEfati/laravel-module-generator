---
title: Laravel Module Generator
hide:
  - navigation
---

<div class="hero">
  <div class="hero__content">
    <h1 class="hero__title">Build cohesive Laravel modules in minutes</h1>
    <p class="hero__lead">Laravel Module Generator bundles migrations, data transfer objects, resources, feature tests, and helper classes so every module ships with the same structure and polish as your best work.</p>
    <div class="hero__actions">
      <a class="md-button md-button--primary" href="installation/">Install the package</a>
      <a class="md-button md-button--secondary" href="usage/">Explore the workflow</a>
      <a class="md-button" href="https://github.com/efati/laravel-module-generator" target="_blank" rel="noopener">Star on GitHub</a>
    </div>
  </div>
</div>

## Why teams choose it

<div class="feature-grid">
  <div class="feature-card">
    <h3>:octicons-rows-16: Schema-driven scaffolding</h3>
    <p>Hydrate DTOs, validation rules, API resources, and feature tests from a single inline schema or by introspecting existing migrations—no more duplicated field metadata.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-circuit-board-16: Container-aware providers</h3>
    <p>Repositories and services are registered automatically, keeping your service container in sync with every module you generate.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-rocket-16: Production-ready defaults</h3>
    <p>Opinionated stubs ship with API response helpers, Jalali-friendly date handling, and optional CRUD test suites so the generated code feels handcrafted.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-code-square-16: Extensible stubs</h3>
    <p>Override any template, wire in custom generators, and tailor namespaces to match the conventions of your organisation.</p>
  </div>
</div>

<div class="quickstart">
  <h2>Quick start</h2>
  <p>Install the package and scaffold your first module with tests, resources, and API endpoints in one command.</p>

```bash
composer require efati/laravel-module-generator
php artisan vendor:publish --tag=module-generator
php artisan make:module Product \
  --api --requests --tests \
  --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
```

  <p>Working without a migration yet? Use the inline schema syntax to describe fields once and reuse the metadata everywhere:</p>

```bash
php artisan make:module Product --fields="name:string:unique, price:decimal(10,2)"
```
</div>

## Learn more

<div class="resource-links">
  <a class="md-button md-button--primary" href="installation/"><span>:material-download-circle:</span><span>Installation guide</span></a>
  <a class="md-button md-button--primary" href="configuration/"><span>:material-tune-variant:</span><span>Configuration reference</span></a>
  <a class="md-button md-button--primary" href="usage/"><span>:material-console:</span><span>Usage &amp; recipes</span></a>
  <a class="md-button md-button--primary" href="advanced/"><span>:material-lan-connect:</span><span>Advanced features</span></a>
  <a class="md-button" href="changelog/"><span>:material-newspaper-variant-multiple:</span><span>Changelog</span></a>
  <a class="md-button" href="github-pages-setup/"><span>:material-github:</span><span>GitHub Pages deployment</span></a>
</div>

Need to ship the docs? See the <a href="github-pages-setup/">GitHub Pages guide</a> for the automated workflow.
