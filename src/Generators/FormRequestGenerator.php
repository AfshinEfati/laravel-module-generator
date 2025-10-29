<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Support\MigrationFieldParser;
use Efati\ModuleGenerator\Support\ModelInspector;
use Efati\ModuleGenerator\Support\SchemaParser;
use Efati\ModuleGenerator\Support\Stub;

class FormRequestGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        bool $force = false,
        ?array $fields = null,
        ?string $migrationTable = null
    ): array {

        $paths  = config('module-generator.paths', []);
        $reqRel = $paths['form_request'] ?? ($paths['requests'] ?? 'Http/Requests');

        $reqPath = app_path($reqRel);
        File::ensureDirectoryExists($reqPath);

        $requestFolder = $reqPath . DIRECTORY_SEPARATOR . $name;
        File::ensureDirectoryExists($requestFolder);

        $modelFqcn  = $baseNamespace . '\\Models\\' . $name;
        $table      = self::guessTable($modelFqcn, $migrationTable);
        $routeParam = lcfirst($name);

        [$storeRules, $updateRules] = self::buildRules($modelFqcn, $table, $fields);


        $requestNamespace = $baseNamespace . '\\Http\\Requests\\' . $name;

        $storeContent  = self::buildRequestClass('Store' . $name . 'Request', $requestNamespace, $storeRules, false, null, null);
        $updateContent = self::buildRequestClass('Update' . $name . 'Request', $requestNamespace, $updateRules, true, $routeParam, $table);

        $storeFile  = $requestFolder . DIRECTORY_SEPARATOR . 'Store' . $name . 'Request.php';
        $updateFile = $requestFolder . DIRECTORY_SEPARATOR . 'Update' . $name . 'Request.php';

        return [
            $storeFile  => self::writeFile($storeFile, $storeContent, $force),
            $updateFile => self::writeFile($updateFile, $updateContent, $force),
        ];
    }

    private static function guessTable(string $modelFqcn, ?string $migrationTable): string
    {
        if ($migrationTable) {
            return $migrationTable;
        }

        if (class_exists($modelFqcn)) {
            $m = new $modelFqcn();
            if (property_exists($m, 'table') && $m->table) {
                return $m->table;
            }
            return Str::snake(Str::pluralStudly(class_basename($modelFqcn)));
        }
        return Str::snake(Str::pluralStudly(class_basename($modelFqcn)));
    }

    private static function buildRules(string $modelFqcn, string $table, ?array $fieldMeta = null): array

    {
        $schema = [];
        if (is_array($fieldMeta)) {
            $schema = $fieldMeta;

            if (!empty($schema)) {
                return MigrationFieldParser::buildValidationRules($schema, $table);
            }
        }

        $fillable = ModelInspector::extractFillable($modelFqcn);

        if (empty($fillable) && !empty($schema)) {
            $fillable = SchemaParser::fieldNames($schema);
        }

        $schemaMap = SchemaParser::keyByName($schema);

        $store = [];
        foreach ($fillable as $f) {
            $definition = $schemaMap[$f] ?? null;
            $rules      = self::inferRuleForField($f, $table, true, $definition);
            if (!empty($rules)) {
                $store[$f] = array_values($rules);
            }
        }

        $update = [];
        foreach ($fillable as $f) {
            $definition = $schemaMap[$f] ?? null;
            $r          = self::inferRuleForField($f, $table, false, $definition);
            if (!empty($r)) {
                array_unshift($r, 'sometimes');
                $update[$f] = array_values($r);
            }
        }

        return [$store, $update];
    }

    private static function inferRuleForField(string $field, string $table, bool $forCreate, ?array $definition = null): array
    {
        if ($definition !== null) {
            return self::rulesFromDefinition($field, $table, $definition);
        }

        if (Str::endsWith($field, '_id')) {
            $base    = substr($field, 0, -3);
            $fkTable = Str::snake(Str::pluralStudly($base));
            return [$forCreate ? 'required' : 'nullable', 'integer', 'exists:' . $fkTable . ',id'];
        }

        if (strpos($field, 'email') !== false) {
            return ['nullable', 'email', 'max:255', 'unique:' . $table . ',' . $field];
        }
        if (strpos($field, 'slug') !== false || strpos($field, 'code') !== false) {
            return ['nullable', 'string', 'max:255', 'unique:' . $table . ',' . $field];
        }
        if (strpos($field, 'url') !== false) {
            return ['nullable', 'url'];
        }
        if (strpos($field, 'date') !== false || Str::endsWith($field, '_at')) {
            return ['nullable', 'date'];
        }
        if (
            strpos($field, 'price') !== false ||
            strpos($field, 'amount') !== false ||
            strpos($field, 'rate') !== false ||
            strpos($field, 'total') !== false
        ) {
            return ['nullable', 'numeric'];
        }
        return ['nullable'];
    }

    private static function rulesFromDefinition(string $field, string $table, array $definition): array
    {
        $nullable = (bool) ($definition['nullable'] ?? false);
        $rules    = [$nullable ? 'nullable' : 'required'];

        $typeRules = self::rulesForType((string) ($definition['type'] ?? 'string'));
        if (!empty($typeRules)) {
            $rules = array_merge($rules, $typeRules);
        }

        if (!empty($definition['unique'])) {
            $rules[] = 'unique:' . $table . ',' . $field;
        }

        if (!empty($definition['foreign']) && is_array($definition['foreign'])) {
            $fkTable  = $definition['foreign']['table'] ?? null;
            $fkColumn = $definition['foreign']['column'] ?? 'id';
            if ($fkTable) {
                $rules[] = 'exists:' . $fkTable . ',' . $fkColumn;
            }
        } elseif (Str::endsWith($field, '_id')) {
            $base    = substr($field, 0, -3);
            $fkTable = Str::snake(Str::pluralStudly($base));
            $rules[] = 'exists:' . $fkTable . ',id';
        }

        return array_values(array_unique($rules));
    }

    private static function rulesForType(string $type): array
    {
        $type = SchemaParser::normalizeType($type);

        return match ($type) {
            'string' => ['string', 'max:255'],
            'text' => ['string'],
            'integer' => ['integer'],
            'numeric' => ['numeric'],
            'boolean' => ['boolean'],
            'date' => ['date'],
            'datetime' => ['date'],
            'json', 'array' => ['array'],
            'uuid' => ['uuid'],
            'email' => ['email', 'max:255'],
            'url' => ['url'],
            default => [],
        };
    }

    private static function buildRequestClass(
        string $className,
        string $requestNamespace,
        array $rules,
        bool $isUpdate,
        ?string $routeParam,
        ?string $table
    ): string {
        $uses = [
            'use Illuminate\\Foundation\\Http\\FormRequest;',
        ];

        $routeAccessor = null;
        if ($isUpdate) {
            $routeAccessor = $routeParam
                ? "\$this->route('{$routeParam}')->id"
                : "\$this->route('id')";
        }

        $rulesBody = self::renderRulesBody($rules, $isUpdate, $routeAccessor);

        $ns = $requestNamespace;

        return Stub::render('FormRequest/request', [
            'namespace'  => $ns,
            'uses'       => implode("\n", $uses),
            'class'      => $className,
            'rules_body' => $rulesBody,
        ]);
    }

    private static function renderRulesBody(array $rules, bool $isUpdate, ?string $routeAccessor): string
    {
        if (empty($rules)) {
            return '        return [];';
        }

        $lines = ['        return ['];

        foreach ($rules as $field => $ruleSet) {
            $lines[] = "            '" . addslashes($field) . "' => " . self::exportRuleSet($ruleSet, $isUpdate, $routeAccessor) . ",";
        }

        $lines[] = '        ];';

        return implode("\n", $lines);
    }

    private static function exportRuleSet($ruleSet, bool $isUpdate, ?string $routeAccessor): string
    {
        if (is_array($ruleSet)) {
            $parts = [];
            foreach ($ruleSet as $rule) {
                $parts[] = self::exportRuleValue($rule, $isUpdate, $routeAccessor);
            }
            return '[' . implode(', ', $parts) . ']';
        }

        return self::exportRuleValue($ruleSet, $isUpdate, $routeAccessor);
    }

    private static function exportRuleValue($rule, bool $isUpdate, ?string $routeAccessor): string
    {
        if (!is_string($rule)) {
            return var_export($rule, true);
        }

        if ($isUpdate && $routeAccessor && str_starts_with($rule, 'unique:')) {
            $body     = substr($rule, 7);
            $segments = array_map('trim', explode(',', $body));
            $hasIgnore = count($segments) >= 3 && $segments[2] !== '';
            if (!$hasIgnore) {
                $prefix = 'unique:' . $body;
                if (!str_ends_with($prefix, ',')) {
                    $prefix .= ',';
                }
                return "'" . addslashes($prefix) . "' . " . $routeAccessor;
            }
        }

        return "'" . addslashes($rule) . "'";
    }

    private static function writeFile(string $path, string $contents, bool $force): bool
    {
        if (!$force && File::exists($path)) {
            return false;
        }

        File::put($path, $contents);

        return true;
    }
}
