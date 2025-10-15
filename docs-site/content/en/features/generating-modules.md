# Generating Modules

The core of this package is the `make:module` Artisan command. It's a powerful tool that can generate a complete, test-ready module with a single command.

## Basic Usage

To generate a new module, you simply need to provide a name for it:

```bash
php artisan make:module ProductName
```

This will generate a `ProductName` module with the default components, which you can configure in the `config/module-generator.php` file.

## Command Options

The `make:module` command comes with a variety of options that allow you to customize the generated module to your specific needs. Here's a breakdown of the available options:

| Option | Alias | Description |
| --- | --- | --- |
| `--api` | `-a` | Generate an API-oriented controller that targets the configured API namespace. |
| `--controller=Subdir` | `-c` | Place the controller inside a subfolder (forces controller generation). |
| `--requests` | `-r` | Generate `Store` and `Update` form requests. |
| `--tests` | `-t` | Force CRUD feature test generation. |
| `--no-controller` | `-nc` | Skip controller generation. |
| `--no-resource` | `-nr` | Skip API Resource generation. |
| `--no-dto` | `-nd` | Skip DTO generation. |
| `--no-test` | `-nt` | Skip feature tests. |
| `--no-provider` | `-np` | Skip provider creation and automatic registration. |
| `--swagger` | `-sg` | Generate OpenAPI (`@OA`) annotations inside `App\Docs\{Module}Doc`. |
| `--no-swagger` | – | Explicitly disable Swagger annotations even when enabled by defaults. |
| `--from-migration=` | `-fm` | Provide a migration path or keyword to infer fields and relations. |
| `--fields=` | – | Inline schema definition (comma-separated) for modules without migrations. |
| `--force` | `-f` | Overwrite existing files instead of skipping them. |
| `--actions` | | Generate Actions for the module. |
| `--no-actions` | | Skip generating Actions. |

### Example with Options

Here's an example of how you can use these options to generate a more customized module:

```bash
php artisan make:module Product --api --requests --tests --swagger
```

This command will generate a `Product` module with an API controller, form requests, feature tests, and Swagger documentation.