<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\MigrationFieldParser;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Support\SchemaParser;

class TestGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        ?string $controllerSubfolder = null,
        bool $force = false,
        ?array $fields = null
    ): array {

        $testsPath = base_path(config('module-generator.tests.feature', 'tests/Feature'));
        File::ensureDirectoryExists($testsPath);

        $className = $name . 'CrudTest';
        $filePath  = $testsPath . '/' . $className . '.php';

        $modelFqcn = $baseNamespace . '\\Models\\' . $name;

        $paths          = config('module-generator.paths', []);
        $controllerRel  = $paths['controller'] ?? ($paths['controllers'] ?? 'Http/Controllers/Api/V1');
        $controllerNs   = self::controllerNamespaceFromRel($baseNamespace, $controllerRel, $controllerSubfolder);
        $controllerFqcn = $controllerNs . '\\' . $name . 'Controller';

        $resourceSegment  = Str::kebab(Str::pluralStudly($name));
        $testRouteSegment = 'test-' . $resourceSegment;
        $baseUri          = '/' . $testRouteSegment;

        $fieldMetadata   = self::resolveFieldMetadata($modelFqcn, $fields, $baseNamespace);
        $fillable        = array_keys($fieldMetadata);
        $fillableExport  = self::exportArray($fillable);
        $metadataExport  = self::exportAssoc($fieldMetadata, 2);


        $content = Stub::render('Test/feature', [
            'class'                  => $className,
            'base_uri'               => $baseUri,
            'test_route_segment'     => $testRouteSegment,
            'controller_fqcn'        => $controllerFqcn,
            'fillable_export'        => $fillableExport,
            'base_namespace_literal' => $baseNsLiteral,
            'model_fqcn'             => $modelFqcn,
        ]);

        $content = <<<PHP
<?php

namespace Tests\\Feature;

use Tests\\TestCase;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Foundation\\Testing\\WithFaker;
use Illuminate\\Support\\Facades\\Route;

class {$className} extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected string \$baseUri = '{$baseUri}';

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->group(function () {
            Route::apiResource('{$testRouteSegment}', \\{$controllerFqcn}::class);
        });
    }

    private function fillable(): array

    {
        $rel = str_replace('/', '\\', trim($controllerRel, '/\\'));
        $ns  = $baseNamespace . '\\' . $rel;
        if ($subfolder) {
            $ns .= '\\' . str_replace(['/', '\\'], '\\', trim($subfolder, '/\\'));
        }
        return $ns;
    }

    private function fieldMetadata(): array
    {
        return {$metadataExport};
    }

    private function buildValidPayload(bool \$forCreate = true): array
    {
        \$payload = [];
        \$canCreate = true;
        \$metadata = \$this->fieldMetadata();

        foreach (\$this->fillable() as \$field) {
            \$meta = \$metadata[\$field] ?? [];

            if (!empty(\$meta['foreign']['related_model'])) {
                \$related = \$meta['foreign']['related_model'];
                \$id = null;
                if (is_string(\$related) && class_exists(\$related)) {
                    if (method_exists(\$related, 'factory')) {
                        \$id = \$related::factory()->create()->getKey();
                    } else {
                        try {
                            \$obj = new \$related();
                            \$fill = method_exists(\$obj, 'getFillable') ? \$obj->getFillable() : [];
                            \$data = [];
                            foreach (\$fill as \$f) {
                                if (str_ends_with(\$f, '_id')) { continue; }
                                if (stripos(\$f, 'email') !== false) { \$data[\$f] = 'x'.uniqid().'@example.test'; continue; }
                                if (stripos(\$f, 'slug') !== false) { \$data[\$f] = 'slug-'.uniqid(); continue; }
                                if (stripos(\$f, 'name') !== false) { \$data[\$f] = 'Name '.uniqid(); continue; }
                                if (stripos(\$f, 'price') !== false || stripos(\$f, 'amount') !== false) { \$data[\$f] = 1; continue; }
                                if (stripos(\$f, 'is_') === 0 || stripos(\$f, 'has_') === 0) { \$data[\$f] = true; continue; }
                                \$data[\$f] = 'val';
                            }
                            \$obj = \$related::query()->create(\$data);
                            \$id = \$obj->getKey();
                        } catch (\Throwable \$e) {}
                    }
                }
                if (\$id === null) {
                    \$canCreate = false;
                } else {
                    \$payload[\$field] = \$id;
                }
                continue;
            }

            \$payload[\$field] = \$this->fakeValueForField(\$field, \$meta);

        }
        $m        = new $modelFqcn();
        $fillable = method_exists($m, 'getFillable') ? $m->getFillable() : [];

        return [\$payload, \$canCreate];
    }

    private function fakeValueForField(string \$field, array \$meta): mixed
    {
        if (!empty(\$meta['enum']) && is_array(\$meta['enum'])) {
            return \$meta['enum'][0];
        }

        \$cast = \$meta['cast'] ?? null;
        if (is_string(\$cast) && str_contains(\$cast, ':')) {
            \$cast = strtolower(strtok(\$cast, ':'));
        }
        \$type = \$meta['type'] ?? null;

        if (stripos(\$field, 'email') !== false) {
            return 'u'.uniqid().'@example.test';
        }
        if (stripos(\$field, 'slug') !== false || stripos(\$field, 'code') !== false) {
            return 'slug-'.uniqid();
        }
        if (stripos(\$field, 'name') !== false || stripos(\$field, 'title') !== false) {
            return 'Title '.uniqid();
        }
        if (stripos(\$field, 'price') !== false || stripos(\$field, 'amount') !== false || stripos(\$field, 'rate') !== false) {
            return 1000;
        }
        if (stripos(\$field, 'is_') === 0 || stripos(\$field, 'has_') === 0) {
            return true;
        }

        if (in_array(\$type, ['boolean'], true) || in_array(\$cast, ['bool', 'boolean'], true)) {
            return true;
        }
        if (in_array(\$type, ['integer'], true) || in_array(\$cast, ['int', 'integer'], true)) {
            return 1;
        }
        if (in_array(\$type, ['float', 'decimal'], true) || in_array(\$cast, ['float', 'double', 'decimal'], true)) {
            return 1.0;
        }
        if (in_array(\$type, ['json'], true) || in_array(\$cast, ['array', 'collection'], true)) {
            return ['sample' => 'data'];
        }
        if (\$type === 'date') {
            return '2024-01-01';
        }
        if (\$type === 'datetime') {
            return '2024-01-01 00:00:00';
        }
        if (\$type === 'uuid') {
            return 'uuid-'.uniqid();
        }

        return 'text';
    }

    private function createModel(): \\{$modelFqcn}
    {
        if (method_exists(\\{$modelFqcn}::class, 'factory')) {
            return \\{$modelFqcn}::factory()->create();

        }

    public function test_index_returns_list(): void
    {
        try {
            if (method_exists(\\{$modelFqcn}::class, 'factory')) {
                \\{$modelFqcn}::factory()->count(3)->create();
            }
        } catch (\Throwable \$e) {}
        \$res = \$this->json('GET', \$this->baseUri);
        \$res->assertStatus(200)->assertJsonStructure(['success', 'message', 'data']);

    }

    private static function exportArray(array $arr): string
    {
        $items = array_map(fn($v) => var_export($v, true), $arr);
        return '[' . implode(', ', $items) . ']';
    }

    private static function exportSchema(array $schema): string
    {
        if (empty($schema)) {
            return '[]';
        }

        $assoc = [];
        foreach ($schema as $field) {
            if (!isset($field['name'])) {
                continue;
            }

            $foreign = null;
            if (!empty($field['foreign']) && is_array($field['foreign'])) {
                $table  = $field['foreign']['table'] ?? null;
                $column = $field['foreign']['column'] ?? 'id';
                if ($table) {
                    $foreign = ['table' => $table, 'column' => $column];
                }
            }

            $assoc[$field['name']] = [
                'type'     => SchemaParser::normalizeType((string) ($field['type'] ?? 'string')),
                'nullable' => (bool) ($field['nullable'] ?? false),
                'unique'   => (bool) ($field['unique'] ?? false),
                'foreign'  => $foreign,
            ];
        }

        if (empty($assoc)) {
            return '[]';
        }

        return self::exportValue($assoc, 2);
    }

    private static function exportValue(mixed $value, int $indent = 0): string
    {
        if (is_array($value)) {
            if ($value === []) {
                return '[]';
            }

            $indentStr     = str_repeat('    ', $indent);
            $nextIndentStr = str_repeat('    ', $indent + 1);
            $lines         = [];

    private static function controllerNamespaceFromRel(string $baseNamespace, string $controllerRel, ?string $subfolder): string
    {
        $rel = str_replace('/', '\\', trim($controllerRel, '/\\'));
        $ns  = $baseNamespace . '\\' . $rel;
        if ($subfolder) {
            $ns .= '\\' . str_replace(['/', '\\'], '\\', trim($subfolder, '/\\'));

        }

    private static function resolveFieldMetadata(string $modelFqcn, ?array $fields, string $baseNamespace): array
    {
        if (is_array($fields) && !empty($fields)) {
            $metadata = MigrationFieldParser::normaliseFieldMetadata($fields, $baseNamespace);
        } else {
            $fillable = self::getFillable($modelFqcn);
            $casts    = [];
            if (class_exists($modelFqcn)) {
                $model = new $modelFqcn();
                $casts = method_exists($model, 'getCasts') ? $model->getCasts() : [];
            }
            $metadata = [];
            foreach ($fillable as $field) {
                $cast = $casts[$field] ?? null;
                $metadata[$field] = [
                    'type'     => self::inferTypeFromName($field, $cast),
                    'cast'     => $cast,
                    'nullable' => true,
                    'unique'   => false,
                ];
                if (str_ends_with($field, '_id')) {
                    $related = Str::studly(str_replace(['-', '_'], ' ', substr($field, 0, -3)));
                    $related = str_replace(' ', '', $related);
                    $metadata[$field]['foreign'] = [
                        'table'         => null,
                        'references'    => 'id',
                        'related_model' => $baseNamespace . '\\Models\\' . $related,
                    ];
                }
            }
        }

        foreach ($metadata as &$meta) {
            if (empty($meta['foreign'])) {
                unset($meta['foreign']);
            }
            if (empty($meta['enum'])) {
                unset($meta['enum']);
            }
            if (!array_key_exists('cast', $meta) || $meta['cast'] === null) {
                unset($meta['cast']);
            }
        }
        unset($meta);

        return $metadata;
    }

    private static function inferTypeFromName(string $field, ?string $cast = null): string
    {
        $normalizedCast = $cast;
        if (is_string($normalizedCast) && str_contains($normalizedCast, ':')) {
            $normalizedCast = strtolower(strtok($normalizedCast, ':'));
        }
        $normalizedCast = is_string($normalizedCast) ? strtolower($normalizedCast) : null;

        return match (true) {
            $normalizedCast === 'bool',
            $normalizedCast === 'boolean',
            str_starts_with($field, 'is_'),
            str_starts_with($field, 'has_') => 'boolean',

            $normalizedCast === 'int',
            $normalizedCast === 'integer',
            str_ends_with($field, '_id') => 'integer',

            $normalizedCast === 'float',
            $normalizedCast === 'double',
            $normalizedCast === 'decimal',
            str_contains($field, 'price'),
            str_contains($field, 'amount'),
            str_contains($field, 'rate') => 'float',

            $normalizedCast === 'array',
            $normalizedCast === 'collection' => 'json',

            $normalizedCast === 'datetime' => 'datetime',
            $normalizedCast === 'date' => 'date',

            $normalizedCast === 'uuid' => 'uuid',
            default => 'string',
        };
    }

    private static function getFillable(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) {
            return [];
        }
        $m = new $modelFqcn();
        return method_exists($m, 'getFillable') ? $m->getFillable() : [];
    }


        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return var_export($value, true);
    }

    private static function exportAssoc(array $arr, int $indentLevel = 0): string
    {
        if ($arr === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $indentLevel);
        $nextIndent = str_repeat('    ', $indentLevel + 1);
        $lines = ['['];

        foreach ($arr as $key => $value) {
            $keyStr = is_int($key) ? '' : "'" . addslashes((string) $key) . "' => ";
            if (is_array($value)) {
                $nested = self::exportAssoc($value, $indentLevel + 1);
                $nestedLines = explode("\n", $nested);
                $nestedLines[0] = $nextIndent . $keyStr . ltrim($nestedLines[0]);
                for ($i = 1; $i < count($nestedLines); $i++) {
                    $nestedLines[$i] = $nextIndent . $nestedLines[$i];
                }
                $nestedLines[count($nestedLines) - 1] .= ',';
                $lines = array_merge($lines, $nestedLines);
            } else {
                $lines[] = $nextIndent . $keyStr . var_export($value, true) . ',';
            }
        }

        $lines[] = $indent . ']';
        return implode("\n", $lines);
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
