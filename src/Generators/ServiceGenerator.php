<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ServiceGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        bool $usesDto = true,
        bool $useInterfaces = true,
        bool $force = false
    ): array {
        $paths = config('module-generator.paths', []);

        // Support both 'service' and 'services' keys + safe defaults
        $servicePaths = $paths['service'] ?? ($paths['services'] ?? []);
        $serviceRel   = is_array($servicePaths) ? ($servicePaths['concretes'] ?? 'Services') : 'Services';
        $contractsRel = is_array($servicePaths) ? ($servicePaths['contracts'] ?? 'Services/Contracts') : 'Services/Contracts';

        $servicePath  = app_path($serviceRel);
        $contractPath = app_path($contractsRel);
        File::ensureDirectoryExists($servicePath);
        File::ensureDirectoryExists($contractPath);

        $repoInterfaceFqcn = "{$baseNamespace}\\Repositories\\Contracts\\{$name}RepositoryInterface";
        $repoConcreteFqcn  = "{$baseNamespace}\\Repositories\\Eloquent\\{$name}Repository";
        $dtoFqcn           = "{$baseNamespace}\\DTOs\\{$name}DTO";

        $serviceInterfacePath = $contractPath . "/{$name}ServiceInterface.php";
        $serviceConcretePath  = $servicePath . "/{$name}Service.php";

        $results = [];

        // Interface generation
        $interfaceUses = [
            'Illuminate\\Database\\Eloquent\\Model',
        ];
        if ($usesDto) {
            $interfaceUses[] = $dtoFqcn;
        }
        $interfaceUsesBlock = 'use ' . implode(";\nuse ", $interfaceUses) . ';';

        $storeSignature  = $usesDto ? "public function store({$name}DTO \\$dto): Model;" : 'public function store(array \\$data): Model;';
        $updateSignature = $usesDto ? "public function update(int \\$id, {$name}DTO \\$dto): bool;" : 'public function update(int \\$id, array \\$data): bool;';

        $interfaceTemplate = <<<PHP
<?php

namespace {$baseNamespace}\\Services\\Contracts;

{$interfaceUsesBlock}

interface {$name}ServiceInterface
{
    public function index();
    public function show(int \\$id): ?Model;
    {$storeSignature}
    {$updateSignature}
    public function destroy(int \\$id): bool;
}
PHP;

        $results[$serviceInterfacePath] = self::writeFile($serviceInterfacePath, $interfaceTemplate, $force);

        // Service generation
        $serviceUses = [
            "{$baseNamespace}\\Services\\Contracts\\{$name}ServiceInterface",
            "{$baseNamespace}\\Services\\BaseService",
            'Illuminate\\Database\\Eloquent\\Model',
        ];

        $repositoryType = $useInterfaces ? "{$name}RepositoryInterface" : "{$name}Repository";
        $repositoryUse  = $useInterfaces ? $repoInterfaceFqcn : $repoConcreteFqcn;
        $serviceUses[]  = $repositoryUse;

        if (!$useInterfaces) {
            $serviceUses[] = $repoInterfaceFqcn;
        }
        if ($usesDto) {
            $serviceUses[] = $dtoFqcn;
        }
        $serviceUses = array_unique($serviceUses);
        $serviceUsesBlock = 'use ' . implode(";\nuse ", $serviceUses) . ';';

        $storeArgument  = $usesDto ? "{$name}DTO \\$dto" : 'array \\$data';
        $updateArgument = $usesDto ? "{$name}DTO \\$dto" : 'array \\$data';
        $storeBody      = $usesDto
            ? '        return $this->repository->store($dto->toArray());'
            : '        return $this->repository->store($data);';
        $updateBody     = $usesDto
            ? '        return $this->repository->update($id, $dto->toArray());'
            : '        return $this->repository->update($id, $data);';

        $serviceTemplate = <<<PHP
<?php

namespace {$baseNamespace}\\Services;

{$serviceUsesBlock}

class {$name}Service extends BaseService implements {$name}ServiceInterface
{
    public function __construct(public {$repositoryType} \\$repository)
    {
        parent::__construct(\$this->repository);
    }

    public function index()
    {
        return \$this->repository->getAll();
    }

    public function show(int \\$id): ?Model
    {
        return \$this->repository->find(\$id);
    }

    public function store({$storeArgument}): Model
    {
{$storeBody}
    }

    public function update(int \\$id, {$updateArgument}): bool
    {
{$updateBody}
    }

    public function destroy(int \\$id): bool
    {
        return \$this->repository->delete(\$id);
    }
}
PHP;

        $results[$serviceConcretePath] = self::writeFile($serviceConcretePath, $serviceTemplate, $force);

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
