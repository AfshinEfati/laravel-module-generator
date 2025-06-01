<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class RepositoryGenerator
{
    public static function generate(string $name): void
    {
        $repoPath = app_path(config('module-generator.paths.repository'));
        $contractPath = app_path(config('module-generator.paths.repository_contract'));

        File::ensureDirectoryExists($repoPath);
        File::ensureDirectoryExists($contractPath);

        $baseNamespace = config('module-generator.base_namespace');

        File::put("{$repoPath}/{$name}Repository.php", "<?php

namespace {$baseNamespace}\\Repositories\\Eloquent;

use {$baseNamespace}\\Models\\{$name};
use {$baseNamespace}\\Repositories\\Eloquent\\BaseRepository;
use {$baseNamespace}\\Repositories\\Contracts\\{$name}RepositoryInterface;

class {$name}Repository extends BaseRepository implements {$name}RepositoryInterface
{
    public function __construct({$name} \$model)
    {
        parent::__construct(\$model);
    }
}
");

        File::put("{$contractPath}/{$name}RepositoryInterface.php", "<?php

namespace {$baseNamespace}\\Repositories\\Contracts;

use {$baseNamespace}\\Repositories\\Contracts\\BaseRepositoryInterface;

interface {$name}RepositoryInterface extends BaseRepositoryInterface
{
    //
}
");
    }
}
