<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class RepositoryGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', bool $force = false): array
    {
        $paths = config('module-generator.paths', []);

        // Support both 'repository' and 'repositories' keys + safe defaults
        $repoPaths     = $paths['repository']  ?? ($paths['repositories']  ?? []);
        $eloquentRel   = is_array($repoPaths) ? ($repoPaths['eloquent']  ?? 'Repositories/Eloquent')  : 'Repositories/Eloquent';
        $contractsRel  = is_array($repoPaths) ? ($repoPaths['contracts'] ?? 'Repositories/Contracts') : 'Repositories/Contracts';

        $eloquentPath = app_path($eloquentRel);
        $contractPath = app_path($contractsRel);
        File::ensureDirectoryExists($eloquentPath);
        File::ensureDirectoryExists($contractPath);

        $modelFqcn = $baseNamespace . '\\Models\\' . $name;

        // Contract
        $contract = "<?php

namespace {$baseNamespace}\\Repositories\\Contracts;

use Illuminate\\Database\\Eloquent\\Model;

interface {$name}RepositoryInterface
{
    public function getAll();
    public function find(int \$id): ?Model;
    public function store(array \$data): Model;
    public function update(int \$id, array \$data): bool;
    public function delete(int \$id): bool;
}
";
        $results = [];
        $contractFile = $contractPath . "/{$name}RepositoryInterface.php";
        $results[$contractFile] = self::writeFile($contractFile, $contract, $force);

        // Concrete
        $concrete = "<?php

namespace {$baseNamespace}\\Repositories\\Eloquent;

use {$baseNamespace}\\Repositories\\Contracts\\{$name}RepositoryInterface;
use {$baseNamespace}\\Repositories\\Eloquent\\BaseRepository;
use {$modelFqcn};
use Illuminate\\Database\\Eloquent\\Model;

class {$name}Repository extends BaseRepository implements {$name}RepositoryInterface
{
    public function __construct(public {$name} \$model)
    {
        parent::__construct(\$this->model);
    }

    public function getAll()
    {
        return \$this->model->query()->latest()->get();
    }

    public function find(int \$id): ?Model
    {
        return \$this->model->find(\$id);
    }

    public function store(array \$data): Model
    {
        return \$this->model->create(\$data);
    }

    public function update(int \$id, array \$data): bool
    {
        \$item = \$this->find(\$id);
        return \$item ? \$item->update(\$data) : false;
    }

    public function delete(int \$id): bool
    {
        \$item = \$this->find(\$id);
        return \$item ? (bool) \$item->delete() : false;
    }
}
";
        $eloquentFile = $eloquentPath . "/{$name}Repository.php";
        $results[$eloquentFile] = self::writeFile($eloquentFile, $concrete, $force);

        return $results;
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
