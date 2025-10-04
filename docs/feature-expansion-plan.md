# Laravel Module Generator – Feature Expansion Plan

This document captures a high-level plan for implementing the eleven feature areas
requested for the generator. Each section summarises the intended scaffolding,
configuration updates, and generator touch points that will be required. The
outline is structured so that individual features can be tackled independently or
layered together as a cohesive upgrade.

## 1. Action / Use-Case Layer (`--actions`)

* Introduce a new `ActionGenerator` responsible for creating:
  * `Actions/BaseAction.php` with common logging/error-handling hooks.
  * Module-specific actions such as `Create{Name}Action` and
    `Update{Name}StatusAction` under `Actions/{Name}/`.
* Extend controllers to resolve actions (instead of calling the service directly)
  when the `--actions` flag is provided.
* Update service binding provider to ensure action dependencies are injected via
  the container.

## 2. Filter Objects / Fluent Filters (`--filters`)

* Replace array-based filter definitions with dedicated filter objects generated
  by a `FilterGenerator`.
* Provide `Filters/BaseFilter.php` with an `apply(Builder $query)` contract and a
  fluent builder interface.
* Generate example fluent filters (e.g. `ProductFilter::byCategory()->byStatus()`)
  and register an interface for extensibility.

## 3. Policy & Authorization Pack (`--policies`)

* Add a `PolicyGenerator` to scaffold policy classes alongside actions with
  action-aligned methods (`view`, `create`, `update`, `delete`).
* Enhance the module service provider stub so that generated policies are
  auto-registered via `Gate::policy` inside the provider’s `boot` method when the
  flag is supplied.

## 4. Domain Events & Jobs (`--events`, `--jobs`)

* Implement generators for domain events (`ProductCreated`, `ProductUpdated`) and
  listeners or jobs that react to those events. Provide job stubs that include
  hooks for bus batching and chaining.
* Wire the module service provider to map events to listeners when these flags
  are active.

## 5. Form Object / Request Mapper (`--form-objects`)

* Create `FormObjects/BaseFormObject.php` encapsulating validation rules and
  transformation logic from `Request` to DTO payloads.
* Generate module-specific form objects usable across HTTP controllers, console
  commands, and tests.

## 6. Testing Blueprints (`--tests`, `--pest`)

* Extend the existing `TestGenerator` so it can emit both PHPUnit and Pest
  blueprints. Create a shared `ModuleTestCase` stub, companion factories, and a
  JSON snapshot helper for verifying API responses.

## 7. Module Manifest & Health Check

* Produce a module manifest (JSON/YAML) summarising metadata such as module name,
  version, migrations, service bindings, and toggled features.
* Add a new Artisan command `module:inspect` that validates bindings, migrations,
  and naming conventions, surfacing warnings when inconsistencies are detected.

## 8. API Resource & Transformer Layer (`--resource`)

* Preserve the existing resource generator while introducing a dedicated flag to
  explicitly opt into resource generation for backwards compatibility.
* Provide transformer stubs for scenarios where developers prefer custom JSON
  transformers over Laravel resources.

## 9. Command Layer (`--command`)

* Add a `ModuleCommandGenerator` to create module-scoped Artisan commands (e.g.
  `php artisan product:sync`) with feature toggles for queueable operations.

## 10. GraphQL / REST Toggle (`--graphql`, `--rest`)

* Allow simultaneous generation of REST and GraphQL endpoints. The GraphQL stub
  should include schema, type, and resolver scaffolding aligned with the module’s
  DTO and action layer.
* REST controllers continue to leverage the existing generator, but the feature
  flags control which transport layers are scaffolded.

---

This roadmap keeps the implementation cohesive by funnelling all new functionality
through feature-specific generators while reusing the shared configuration and
stub infrastructure already present in the package. Each bullet represents a
work item that can be implemented and tested incrementally to reach full parity
with the requested feature checklist.
