<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class TestGenerator
{
    public static function generate(string $name): void
    {
        $testPath = base_path(config('module-generator.tests.feature'));
        File::ensureDirectoryExists($testPath);

        $content = "<?php

namespace Tests\\Feature;

use Tests\\TestCase;

class {$name}Test extends TestCase
{
    /** @test */
    public function example(): void
    {
        \$this->assertTrue(true);
    }
}
";
        File::put($testPath . "/{$name}Test.php", $content);
    }
}
