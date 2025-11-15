---
title: Laravel Module Generator
---

<a href="/fa/" class="language-switcher">🇮🇷 فارسی</a>

<section class="hero" markdown="1">
  <div class="hero__content" markdown="1">
    <p class="hero__eyebrow">Laravel Module Generator</p>
    <h1 class="hero__title">Scaffold Laravel modules in minutes</h1>
    <p class="hero__lead">
      Generate controllers, DTOs, services, repositories, requests, tests, and documentation from a single Artisan command.
      Ship production-ready modules that follow best practices and keep your project structure predictable.
    </p>
    <div class="hero__actions">
      <a href="https://github.com/AfshinEfati/laravel-module-generator" class="md-button md-button--secondary" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
          <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
        </svg>
        View on GitHub
      </a>
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

## Core Features

- **Repository Pattern** – Auto-generated Eloquent repositories with dynamic query methods
- **Service Layer** – Business logic separation with dependency injection
- **Data Transfer Objects** – Type-safe DTOs with validation and casting
- **Form Requests** – Automatic validation rules based on schema
- **API Resources** – JSON response formatting with relationship loading
- **Feature Tests** – CRUD test suites with inferred payloads
- **Actions Layer** – Invokable action classes for clean separation
- **OpenAPI Docs** – Swagger annotations for automatic API documentation
- **Jalali Support** – Persian calendar helpers and Carbon macros
- **Smart Stubs** – Customizable templates for all generated files

## Keep exploring

- Follow the [installation checklist](/en/installation) to get started with the package.
- Use the [quickstart recipes](/en/quickstart) for inline schemas or existing migrations.
- Explore [core features](/en/features/generating-modules) to understand each component.
- Check [advanced patterns](/en/features/action-layer) and the [CLI reference](/en/reference) for more control.
