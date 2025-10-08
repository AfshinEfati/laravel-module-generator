<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Support\MigrationFieldParser;
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

        $modelFqcn  = $baseNamespace . '\\Models\\' . $name;
        $table      = self::guessTable($modelFqcn, $migrationTable);
        $routeParam = lcfirst($name);

        [$storeRules, $updateRules] = self::buildRules($modelFqcn, $table, $fields);


        $storeContent  = self::buildRequestClass('Store' . $name . 'Request', $baseNamespace, $storeRules, false, null, null);
        $updateContent = self::buildRequestClass('Update' . $name . 'Request', $baseNamespace, $updateRules, true, $routeParam, $table);

        $storeFile  = $reqPath . '/Store' . $name . 'Request.php';
        $updateFile = $reqPath . '/Update' . $name . 'Request.php';

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

        $fillable = [];
        if (class_exists($modelFqcn)) {
            $m = new $modelFqcn();
            $fillable = method_exists($m, 'getFillable') ? (array) $m->getFillable() : [];
        }

        if (empty($fillable) && !empty($schema)) {
            $fillable = SchemaParser::fieldNames($schema);
        }

        $schemaMap = SchemaParser::keyByName($schema);

        $store = [];
        foreach ($fillable as $f) {
            $definition = $schemaMap[$f] ?? null;
            $rules      = self::inferRuleForField($f, $table, true, $definition);
            if (!empty($rules)) {
                $store[$f] = implode('|', $rules);
            }
        }

        $update = [];
        foreach ($fillable as $f) {
            $definition = $schemaMap[$f] ?? null;
            $r          = self::inferRuleForField($f, $table, false, $definition);
            if (!empty($r)) {
                array_unshift($r, 'sometimes');
                $update[$f] = implode('|', $r);
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
        string $baseNamespace,
        array $rules,
        bool $isUpdate,
        ?string $routeParam,
        ?string $table
    ): string {
        $rulesExport = [];
        foreach ($rules as $k => $v) {
            $rulesExport[] = "            '" . $k . "' => '" . $v . "',";
        }
        $rulesStr = implode("\n", $rulesExport);

        $uses = [
            'use Illuminate\\Foundation\\Http\\FormRequest;',
        ];

        $rulesBody = '';

        if ($isUpdate) {
            $uses[] = 'use Illuminate\\Validation\\Rule;';
            $routeParamVar = $routeParam ? ("'" . $routeParam . "'") : "'id'";
            $tableVal      = $table ? ("'" . $table . "'") : "'items'";

            $rulesInit = $rulesStr === ''
                ? '        $rules = [];'
                : "        \$rules = [\n{$rulesStr}\n        ];";

            $uniquePatch = <<<PHP

        // Convert 'unique:table,field' to Rule::unique(...)->ignore(\$id)
        foreach (\$rules as \$field => &\$pipe) {
            if (!is_string(\$pipe)) {
                continue;
            }
            \$parts = explode('|', \$pipe);
            foreach (\$parts as &\$p) {
                if (strpos(\$p, 'unique:') === 0) {
                    \$p2 = substr(\$p, 7);
                    \$tmp = explode(',', \$p2, 2);
                    \$tbl = \$tmp[0] !== '' ? \$tmp[0] : {$tableVal};
                    \$col = isset(\$tmp[1]) && \$tmp[1] !== '' ? \$tmp[1] : \$field;

                    \$id = null;
                    \$routeParam = \$this->route({$routeParamVar});
                    if (\$routeParam) {
                        if (is_object(\$routeParam) && method_exists(\$routeParam, 'getKey')) {
                            \$id = \$routeParam->getKey();
                        } elseif (is_numeric(\$routeParam)) {
                            \$id = (int) \$routeParam;
                        }
                    }

                    \$p = Rule::unique(\$tbl, \$col)->ignore(\$id);
                }
            }
            unset(\$p);
            \$pipe = \$parts;
        }
        unset(\$pipe);
PHP;

            $bodyParts = [
                $rulesInit,
                $uniquePatch,
                '',
                '        foreach ($rules as $k => &$arr) {',
                '            if (is_array($arr)) {',
                '                $arr = array_map(function ($x) { return $x; }, $arr);',
                '            }',
                '        }',
                '        unset($arr);',
                '',
                '        return $rules;',
            ];

            $rulesBody = implode("\n", $bodyParts);
        } else {
            $rulesBody = $rulesStr === ''
                ? '        return [];'
                : "        return [\n{$rulesStr}\n        ];";
        }

        $ns = $baseNamespace . '\\Http\\Requests';

        return Stub::render('FormRequest/request', [
            'namespace'  => $ns,
            'uses'       => implode("\n", $uses),
            'class'      => $className,
            'rules_body' => $rulesBody,
        ]);
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
