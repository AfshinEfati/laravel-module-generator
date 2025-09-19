<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Efati\ModuleGenerator\Support\SchemaParser;

class DTOGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', bool $force = false, array $schema = []): array
    {
        $paths = config('module-generator.paths', []);
        $dtoRel = $paths['dto'] ?? ($paths['dtos'] ?? 'DTOs');

        $dtoPath = app_path($dtoRel);
        File::ensureDirectoryExists($dtoPath);

        $className = "{$name}DTO";
        $filePath  = $dtoPath . "/{$className}.php";

        $modelFqcn = "{$baseNamespace}\\Models\\{$name}";
        $fillable  = self::getFillable($modelFqcn, $schema);

        $content   = self::build($className, $baseNamespace, $fillable);

        return [$filePath => self::writeFile($filePath, $content, $force)];
    }

    private static function getFillable(string $modelFqcn, array $schema): array
    {
        if (!class_exists($modelFqcn)) {
            return SchemaParser::fieldNames($schema);
        }
        $model = new $modelFqcn();
        $fillable = method_exists($model, 'getFillable') ? $model->getFillable() : [];

        if (empty($fillable)) {
            return SchemaParser::fieldNames($schema);
        }

        return $fillable;
    }

    private static function build(string $className, string $baseNamespace, array $fillable): string
    {
        $ns = "{$baseNamespace}\\DTOs";

        $props = [];
        $ctor  = [];
        $asg   = [];
        foreach ($fillable as $f) {
            $props[] = "    public mixed \${$f};";
            $ctor[]  = "        mixed \${$f} = null";
            $asg[]   = "        \$this->{$f} = \${$f};";
        }

        $ctorSig = implode(",\n", $ctor);
        $asgBody = implode("\n", $asg);

        $fromReq = [];
        foreach ($fillable as $f) {
            $fromReq[] = "            \$dto->{$f} = \$request->input('{$f}');";
        }
        $fromReqBody = implode("\n", $fromReq);

        $toArray = empty($fillable) ? '' : implode("\n", array_map(fn($f) => "        if (\$this->{$f} !== null) { \$out['{$f}'] = \$this->{$f}; }", $fillable));

        return "<?php

namespace {$ns};

use Illuminate\\Http\\Request;

class {$className}
{
" . (empty($props) ? '' : implode("\n", $props) . "\n") . "
    public function __construct(
{$ctorSig}
    ) {
{$asgBody}
    }

    public static function fromRequest(Request \$request): self
    {
        \$dto = new self();
{$fromReqBody}
        return \$dto;
    }

    public function toArray(): array
    {
        \$out = [];
{$toArray}
        return \$out;
    }
}
";
    }

    private static function writeFile(string $path, string $content, bool $force): bool
    {
        if (!$force && File::exists($path)) {
            return false;
        }

        File::put($path, $content);

        return true;
    }
}
