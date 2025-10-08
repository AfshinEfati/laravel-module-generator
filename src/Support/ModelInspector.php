<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelInspector
{
    /**
     * Attempt to determine the model fields that should be treated as fillable.
     */
    public static function extractFillable(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) {
            return [];
        }

        $model = new $modelFqcn();

        $fillable = [];
        if (method_exists($model, 'getFillable')) {
            $fillable = array_filter((array) $model->getFillable(), static fn ($value) => is_string($value) && $value !== '');
            if (!empty($fillable)) {
                return array_values(array_unique($fillable));
            }
        }

        if (property_exists($model, 'fillable') && is_array($model->fillable)) {
            $fillable = array_filter($model->fillable, static fn ($value) => is_string($value) && $value !== '');
            if (!empty($fillable)) {
                return array_values(array_unique($fillable));
            }
        }

        return self::extractColumnsFromModel($model);
    }

    private static function extractColumnsFromModel(object $model): array
    {
        if (!$model instanceof EloquentModel) {
            return [];
        }

        if (!method_exists($model, 'getConnection') || !method_exists($model, 'getTable')) {
            return [];
        }

        try {
            $connection = $model->getConnection();
            if ($connection === null) {
                return [];
            }

            $schema = $connection->getSchemaBuilder();
            if ($schema === null) {
                return [];
            }

            $columns = $schema->getColumnListing($model->getTable());
        } catch (\Throwable $e) {
            return [];
        }

        $columns = array_filter($columns, static fn ($value) => is_string($value) && $value !== '');

        return array_values(array_unique($columns));
    }
}
