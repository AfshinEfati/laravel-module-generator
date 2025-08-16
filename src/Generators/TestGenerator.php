<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', ?string $controllerSubfolder = null): void
    {
        $testsPath = base_path(config('module-generator.tests.feature', 'tests/Feature'));
        File::ensureDirectoryExists($testsPath);

        $className = $name . 'CrudTest';
        $filePath  = $testsPath . '/' . $className . '.php';

        $modelFqcn = $baseNamespace . '\\Models\\' . $name;

        // NS کنترلر را مثل ControllerGenerator از مسیر کانفیگ می‌سازیم
        $paths          = config('module-generator.paths', []);
        $controllerRel  = $paths['controller'] ?? ($paths['controllers'] ?? 'Http/Controllers/Api/V1');
        $controllerNs   = self::controllerNamespaceFromRel($baseNamespace, $controllerRel, $controllerSubfolder);
        $controllerFqcn = $controllerNs . '\\' . $name . 'Controller';

        $resourceSegment   = Str::kebab(Str::pluralStudly($name)); // products
        $testRouteSegment  = 'test-' . $resourceSegment;
        $baseUri           = '/' . $testRouteSegment;

        $fillable       = self::getFillable($modelFqcn);
        $fillableExport = self::exportArray($fillable);

        // baseNamespace را به‌صورت literal امن داخل کد تست قرار می‌دهیم
        $baseNsLiteral = var_export($baseNamespace, true);

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

        // روت‌های آزمایشی مستقل از روت‌های پروژه
        Route::middleware('api')->group(function () {
            Route::apiResource('{$testRouteSegment}', \\{$controllerFqcn}::class);
        });
    }

    /**
     * فیلدهای fillable مدل
     */
    private function fillable(): array
    {
        return {$fillableExport};
    }

    /**
     * ساخت payload معتبر/نسبتاً معتبر برای store/update
     * خروجی: [payload, canCreate]
     */
    private function buildValidPayload(bool \$forCreate = true): array
    {
        \$payload = [];
        \$canCreate = true;

        foreach (\$this->fillable() as \$field) {
            if (str_ends_with(\$field, '_at')) {
                continue;
            }
            if (str_ends_with(\$field, '_id')) {
                \$base = substr(\$field, 0, -3);
                \$related = {$baseNsLiteral} . '\\\\Models\\\\' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', \$base)));

                \$id = null;
                if (class_exists(\$related)) {
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
                                if (stripos(\$f, 'slug')  !== false) { \$data[\$f] = 'slug-'.uniqid(); continue; }
                                if (stripos(\$f, 'name')  !== false) { \$data[\$f] = 'Name '.uniqid(); continue; }
                                if (stripos(\$f, 'price') !== false || stripos(\$f, 'amount') !== false) { \$data[\$f] = 1; continue; }
                                if (stripos(\$f, 'is_') === 0 || stripos(\$f, 'has_') === 0) { \$data[\$f] = true; continue; }
                                \$data[\$f] = 'val';
                            }
                            \$obj = \$related::query()->create(\$data);
                            \$id = \$obj->getKey();
                        } catch (\\Throwable \$e) {}
                    }
                }
                if (\$id === null) {
                    \$canCreate = false;
                } else {
                    \$payload[\$field] = \$id;
                }
                continue;
            }

            if (stripos(\$field, 'email') !== false) {
                \$payload[\$field] = 'u'.uniqid().'@example.test';
            } elseif (stripos(\$field, 'slug') !== false || stripos(\$field, 'code') !== false) {
                \$payload[\$field] = 'slug-'.uniqid();
            } elseif (stripos(\$field, 'name') !== false || stripos(\$field, 'title') !== false) {
                \$payload[\$field] = 'Title '.uniqid();
            } elseif (stripos(\$field, 'price') !== false || stripos(\$field, 'amount') !== false || stripos(\$field, 'rate') !== false) {
                \$payload[\$field] = 1000;
            } elseif (stripos(\$field, 'is_') === 0 || stripos(\$field, 'has_') === 0) {
                \$payload[\$field] = true;
            } else {
                \$payload[\$field] = 'text';
            }
        }

        return [\$payload, \$canCreate];
    }

    private function createModel(): \\{$modelFqcn}
    {
        if (method_exists(\\{$modelFqcn}::class, 'factory')) {
            return \\{$modelFqcn}::factory()->create();
        }
        [\$payload, \$can] = \$this->buildValidPayload(true);
        return \\{$modelFqcn}::query()->create(\$payload);
    }

    public function test_index_returns_list(): void
    {
        try {
            if (method_exists(\\{$modelFqcn}::class, 'factory')) {
                \\{$modelFqcn}::factory()->count(3)->create();
            }
        } catch (\\Throwable \$e) {}
        \$res = \$this->json('GET', \$this->baseUri);
        \$res->assertStatus(200)->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_store_creates_resource_with_valid_data_or_422_when_unresolvable_fk(): void
    {
        [\$payload, \$canCreate] = \$this->buildValidPayload(true);
        \$res = \$this->postJson(\$this->baseUri, \$payload);
        if (\$canCreate) {
            \$res->assertStatus(201)->assertJson(['success' => true]);
        } else {
            \$res->assertStatus(422);
        }
    }

    public function test_store_returns_validation_error_for_duplicate_unique_when_applicable(): void
    {
        if (!in_array('slug', \$this->fillable(), true)) {
            \$this->markTestSkipped('no unique-like field (slug) to test duplication');
        }
        \$existing = \$this->createModel();
        [\$payload, \$can] = \$this->buildValidPayload(true);
        \$payload['slug'] = \$existing->slug;
        \$res = \$this->postJson(\$this->baseUri, \$payload);
        \$res->assertStatus(422);
    }

    public function test_show_returns_single_resource(): void
    {
        \$model = \$this->createModel();
        \$res = \$this->getJson(\$this->baseUri . '/' . \$model->getKey());
        \$res->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_show_returns_404_for_missing_resource(): void
    {
        \$res = \$this->getJson(\$this->baseUri . '/999999999');
        \$res->assertStatus(404);
    }

    public function test_update_updates_resource_with_valid_data(): void
    {
        \$model = \$this->createModel();
        [\$payload, \$can] = \$this->buildValidPayload(false);
        foreach (\$payload as \$k => \$v) {
            if (is_string(\$v) && !str_ends_with(\$k, '_id')) {
                \$payload[\$k] = 'updated-' . uniqid();
                break;
            }
        }
        \$res = \$this->patchJson(\$this->baseUri . '/' . \$model->getKey(), \$payload);
        (\$payload ? \$res->assertStatus(200) : \$res->assertStatus(422));
    }

    public function test_update_returns_validation_error_on_duplicate_unique_when_applicable(): void
    {
        if (!in_array('slug', \$this->fillable(), true)) {
            \$this->markTestSkipped('no unique-like field (slug) to test duplication on update');
        }
        \$a = \$this->createModel();
        \$b = \$this->createModel();
        \$res = \$this->patchJson(\$this->baseUri . '/' . \$b->getKey(), ['slug' => \$a->slug]);
        \$res->assertStatus(422);
    }

    public function test_destroy_deletes_resource(): void
    {
        \$model = \$this->createModel();
        \$res = \$this->deleteJson(\$this->baseUri . '/' . \$model->getKey());
        \$res->assertStatus(204);
    }

    public function test_destroy_returns_404_for_missing_resource(): void
    {
        \$res = \$this->deleteJson(\$this->baseUri . '/999999999');
        \$res->assertStatus(404);
    }
}
PHP;

        File::put($filePath, $content);
    }

    private static function controllerNamespaceFromRel(string $baseNamespace, string $controllerRel, ?string $subfolder): string
    {
        $rel = str_replace('/', '\\', trim($controllerRel, '/\\')); // e.g. Http/Controllers/Api/V1
        $ns  = $baseNamespace . '\\' . $rel;
        if ($subfolder) {
            $ns .= '\\' . str_replace(['/', '\\'], '\\', trim($subfolder, '/\\'));
        }
        return $ns;
    }

    private static function getFillable(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) return [];
        $m = new $modelFqcn();
        return method_exists($m, 'getFillable') ? $m->getFillable() : [];
    }

    private static function exportArray(array $arr): string
    {
        $items = array_map(fn($v) => var_export($v, true), $arr);
        return '[' . implode(', ', $items) . ']';
    }
}
