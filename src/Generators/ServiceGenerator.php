<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ServiceGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App'): void
    {
        $paths = config('module-generator.paths', []);

        // Support both 'service' and 'services' keys + safe defaults
        $servicePaths   = $paths['service'] ?? ($paths['services'] ?? []);
        $serviceRel     = is_array($servicePaths) ? ($servicePaths['concretes'] ?? 'Services')          : 'Services';
        $contractsRel   = is_array($servicePaths) ? ($servicePaths['contracts'] ?? 'Services/Contracts') : 'Services/Contracts';

        $servicePath  = app_path($serviceRel);
        $contractPath = app_path($contractsRel);
        File::ensureDirectoryExists($servicePath);
        File::ensureDirectoryExists($contractPath);

        $repoIface = "{$baseNamespace}\\Repositories\\Contracts\\{$name}RepositoryInterface";
        $dtoFqcn   = "{$baseNamespace}\\DTOs\\{$name}DTO";

        // Interface
        $iface = "<?php

namespace {$baseNamespace}\\Services\\Contracts;

use Illuminate\\Database\\Eloquent\\Model;
use {$dtoFqcn};

interface {$name}ServiceInterface
{
    public function index();
    public function show(int \$id): ?Model;
    public function store({$name}DTO \$dto): Model;
    public function update(int \$id, {$name}DTO \$dto): bool;
    public function destroy(int \$id): bool;
}
";
        File::put($contractPath . "/{$name}ServiceInterface.php", $iface);

        // Service
        $service = "<?php

namespace {$baseNamespace}\\Services;

use {$baseNamespace}\\Services\\Contracts\\{$name}ServiceInterface;
use {$baseNamespace}\\Repositories\\Contracts\\{$name}RepositoryInterface;
use {$baseNamespace}\\Services\\BaseService;
use {$dtoFqcn};
use Illuminate\\Database\\Eloquent\\Model;

class {$name}Service extends BaseService implements {$name}ServiceInterface
{
    public function __construct(public {$name}RepositoryInterface \$repository)
    {
        parent::__construct(\$this->repository);
    }

    public function index()
    {
        return \$this->repository->getAll();
    }

    public function show(int \$id): ?Model
    {
        return \$this->repository->find(\$id);
    }

    public function store({$name}DTO \$dto): Model
    {
        return \$this->repository->store(\$dto->toArray());
    }

    public function update(int \$id, {$name}DTO \$dto): bool
    {
        return \$this->repository->update(\$id, \$dto->toArray());
    }

    public function destroy(int \$id): bool
    {
        return \$this->repository->delete(\$id);
    }
}
";
        File::put($servicePath . "/{$name}Service.php", $service);
    }
}
