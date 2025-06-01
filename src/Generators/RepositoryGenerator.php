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

namespace App\\Repositories\\Eloquent;

use App\\Models\\{$name};
use App\\Repositories\\Base\\BaseRepository;
use App\\Repositories\\Contracts\\{$name}RepositoryInterface;

class {$name}Repository extends BaseRepository implements {$name}RepositoryInterface
{
    public function __construct({$name} \$model)
    {
        parent::__construct(\$model);
    }
}
");

        File::put("{$contractPath}/{$name}RepositoryInterface.php", "<?php

namespace App\\Repositories\\Contracts;

use App\\Repositories\\Base\\BaseRepositoryInterface;

interface {$name}RepositoryInterface extends BaseRepositoryInterface
{
    //
}
");
    }
}
