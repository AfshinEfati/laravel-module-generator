<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RepositoryGenerator
{
    public static function generate(string $name): void
    {
        $repoPath = app_path(config('module-generator.paths.repository'));
        $contractPath = app_path(config('module-generator.paths.repository_contract'));

        File::ensureDirectoryExists($repoPath);
        File::ensureDirectoryExists($contractPath);

        File::put("{$repoPath}/{$name}Repository.php", "<?php

namespace App\\Repositories\\Eloquent;

class {$name}Repository implements \\App\\Repositories\\Contracts\\{$name}RepositoryInterface
{
    //
}
");

        File::put("{$contractPath}/{$name}RepositoryInterface.php", "<?php

namespace App\\Repositories\\Contracts;

interface {$name}RepositoryInterface
{
    //
}
");
    }
}
