# Changelog

All notable changes to this package will be documented in this file.

## [4.0.0] - 2025-06-04

### ✨ Added
- `FormRequestGenerator`: generates Store/Update requests with auto validation rules based on migrations and model fields.
- `ControllerGenerator`: generates full RESTful API controllers with service injection, resource usage, and request validation.
- `ResourceGenerator`: generates JsonResource with auto-detected fields, boolean formatting, date formatting, and eager loaded relations.
- `StatusHelper`: added as default helper for standardized success/error API responses and data formatting.
- Auto-discovery of model relations (`BelongsTo`, `HasOne`, etc.) for eager loading in `show` and `update` methods.

### 🔧 Changed
- New simplified CLI flags:
  - `--controller=Admin` instead of `--with-controller`
  - `--requests` instead of `--with-form-requests`
- Controller path and namespace auto-adjusted with subfolder support.
- Automatically generates and uses correct FormRequest and Resource class names.

### 🛠 Fixed
- Fixed `use` statement path resolution to prevent invalid slashes.
- Fixed wrong injection of model in load statements (`$this->model` → `$model`).
- Fixed namespacing in published files.

---

## [3.2.0] and earlier

Legacy versions only supported basic generation of:
- Repository / Interface
- Service / Interface
- DTO
- Provider
- Empty Controller (optional)