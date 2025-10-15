# Schema-Aware Generation

One of the most powerful features of this package is its ability to generate code that is aware of your module's schema. This means that the generator can automatically pre-fill DTOs, validation rules, casts, and test payloads based on your database schema.

There are two ways to provide the schema to the generator: from an existing migration or by defining it inline with the `--fields` option.

## From a Migration

If you have already created a migration for your module's model, you can use the `--from-migration` option to tell the generator to infer the schema from it.

```bash
php artisan make:module Product --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
```

The generator will parse the migration file and extract the columns, relations, casts, and validation constraints. This information will then be used to generate the module's components.

You can also provide a keyword to the `--from-migration` option, and the generator will try to find a migration that matches the model name.

```bash
php artisan make:module Product --from-migration
```

## Inline Schema Definition

If you haven't created a migration yet, you can define the schema inline using the `--fields` option. The schema is defined as a comma-separated string of field definitions.

Each field definition has the following format: `name:type:modifiers`.

- `name`: The name of the field.
- `type`: The type of the field (e.g., `string`, `integer`, `decimal`, `boolean`).
- `modifiers`: A comma-separated list of modifiers (e.g., `unique`, `nullable`, `foreign=users.id`).

Here's an example of how to define a schema inline:

```bash
php artisan make:module Product --fields="name:string:unique,price:decimal(10,2),is_active:boolean"
```

This command will generate a `Product` module with a `name` field that is a unique string, a `price` field that is a decimal with 10 digits and 2 decimal places, and an `is_active` field that is a boolean.

### Foreign Keys

You can also define foreign keys in the inline schema. Here's an example:

```bash
php artisan make:module Product --fields="name:string,user_id:foreign=users.id"
```

This will generate a `Product` module with a `user_id` field that is a foreign key to the `id` column of the `users` table. The generator will also automatically add a `belongsTo` relationship to the `User` model in the generated `Product` model.