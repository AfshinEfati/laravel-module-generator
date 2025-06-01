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

        File::put("{$repoPath}/{$name}Repository.php", "<?php

namespace {base_namespace}\\Repositories\\Eloquent;;

use {base_namespace}\\Models\\{$name};;
use {base_namespace}\\Repositories\\Base\\BaseRepository;;
use {base_namespace}\\Repositories\\Contracts\\{$name}RepositoryInterface;;

class {$name}Repository extends BaseRepository implements {$name}RepositoryInterface
{
    public function __construct({$name} \$model)
    {
        parent::__construct(\$model);
    }
}
");

        File::put("{$contractPath}/{$name}RepositoryInterface.php", "<?php

namespace {base_namespace}\\Repositories\\Contracts;;

use {base_namespace}\\Repositories\\Base\\BaseRepositoryInterface;;

interface {$name}RepositoryInterface extends BaseRepositoryInterface
{
    //
}
");
    }
}