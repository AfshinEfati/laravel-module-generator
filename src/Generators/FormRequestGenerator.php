<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FormRequestGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App'): void
    {
        $paths  = config('module-generator.paths', []);
        $reqRel = $paths['form_request'] ?? ($paths['requests'] ?? 'Http/Requests');

        $reqPath = app_path($reqRel);
        File::ensureDirectoryExists($reqPath);

        $modelFqcn  = $baseNamespace . '\\Models\\' . $name;
        $table      = self::guessTable($modelFqcn);
        $routeParam = lcfirst($name);

        [$storeRules, $updateRules] = self::buildRules($modelFqcn, $table);

        $storeContent  = self::buildRequestClass('Store' . $name . 'Request', $baseNamespace, $storeRules, false, null, null);
        $updateContent = self::buildRequestClass('Update' . $name . 'Request', $baseNamespace, $updateRules, true, $routeParam, $table);

        if (File::put($reqPath . '/Store' . $name . 'Request.php', $storeContent) === false) {
            throw new \RuntimeException('Failed to write Store' . $name . 'Request.php');
        }
        if (File::put($reqPath . '/Update' . $name . 'Request.php', $updateContent) === false) {
            throw new \RuntimeException('Failed to write Update' . $name . 'Request.php');
        }
    }

    private static function guessTable(string $modelFqcn): string
    {
        if (class_exists($modelFqcn)) {
            $m = new $modelFqcn();
            if (property_exists($m, 'table') && $m->table) {
                return $m->table;
            }
            return Str::snake(Str::pluralStudly(class_basename($modelFqcn)));
        }
        return Str::snake(Str::pluralStudly(class_basename($modelFqcn)));
    }

    private static function buildRules(string $modelFqcn, string $table): array
    {
        $fillable = [];
        if (class_exists($modelFqcn)) {
            $m = new $modelFqcn();
            $fillable = method_exists($m, 'getFillable') ? $m->getFillable() : [];
        }

        $store = [];
        foreach ($fillable as $f) {
            $store[$f] = implode('|', self::inferRuleForField($f, $table, true));
        }

        $update = [];
        foreach ($fillable as $f) {
            $r = self::inferRuleForField($f, $table, false);
            array_unshift($r, 'sometimes');
            $update[$f] = implode('|', $r);
        }

        return [$store, $update];
    }

    private static function inferRuleForField(string $field, string $table, bool $forCreate): array
    {
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

        $uses = "use Illuminate\\Foundation\\Http\\FormRequest;";
        $uniquePatch = '';

        if ($isUpdate) {
            $uses .= "\nuse Illuminate\\Validation\\Rule;";
            $routeParamVar = $routeParam ? ("'" . $routeParam . "'") : "'id'";
            $tableVal      = $table ? ("'" . $table . "'") : "'items'";

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
        }

        $rulesBody = $isUpdate
            ? <<<PHP

        \$rules = [
{$rulesStr}
        ];{$uniquePatch}

        foreach (\$rules as \$k => &\$arr) {
            if (is_array(\$arr)) {
                \$arr = array_map(function (\$x) { return \$x; }, \$arr);
            }
        }
        unset(\$arr);

        return \$rules;
PHP
            : <<<PHP

        return [
{$rulesStr}
        ];
PHP;

        $ns = $baseNamespace . '\\Http\\Requests';

        return <<<PHP
<?php

namespace {$ns};

{$uses}

class {$className} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {{$rulesBody}
}
}
PHP;
    }
}
