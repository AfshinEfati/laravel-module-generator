<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
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

        $storeSignature  = $usesDto ? "public function store({$name}DTO \\$dto): Model;" : 'public function store(array \\$data): Model;';
        $updateSignature = $usesDto ? "public function update(int \\$id, {$name}DTO \\$dto): bool;" : 'public function update(int \\$id, array \\$data): bool;';

        $interfaceContent = Stub::render('Service/interface', [
            'namespace'        => $baseNamespace . '\\Services\\Contracts',
            'uses'             => self::buildUses($interfaceUses),
            'interface'        => $name . 'ServiceInterface',
            'store_signature'  => $storeSignature,
            'update_signature' => $updateSignature,
        ]);

        $results[$serviceInterfacePath] = self::writeFile($serviceInterfacePath, $interfaceContent, $force);

        // Service generation
        $serviceUses = [
            $baseNamespace . '\\Services\\Contracts\\' . $name . 'ServiceInterface',
            $baseNamespace . '\\Services\\BaseService',
            'Illuminate\\Database\\Eloquent\\Model',
        ];

        $repositoryType = $useInterfaces ? $name . 'RepositoryInterface' : $name . 'Repository';
        $repositoryUse  = $useInterfaces ? $repoInterfaceFqcn : $repoConcreteFqcn;
        $serviceUses[]  = $repositoryUse;

        if (!$useInterfaces) {
            $serviceUses[] = $repoInterfaceFqcn;
        }
        if ($usesDto) {
            $serviceUses[] = $dtoFqcn;
        }

        $storeArgument  = $usesDto ? "{$name}DTO \\$dto" : 'array \\$data';
        $updateArgument = $usesDto ? "{$name}DTO \\$dto" : 'array \\$data';
        $storeBody      = $usesDto
            ? '        return $this->repository->store($dto->toArray());'
            : '        return $this->repository->store($data);';
        $updateBody     = $usesDto
            ? '        return $this->repository->update($id, $dto->toArray());'
            : '        return $this->repository->update($id, $data);';

        $serviceContent = Stub::render('Service/concrete', [
            'namespace'        => $baseNamespace . '\\Services',
            'uses'             => self::buildUses($serviceUses),
            'class'            => $name . 'Service',
            'interface'        => $name . 'ServiceInterface',
            'repository_type'  => $repositoryType,
            'store_argument'   => $storeArgument,
            'update_argument'  => $updateArgument,
            'store_body'       => $storeBody,
            'update_body'      => $updateBody,
        ]);

        $results[$serviceConcretePath] = self::writeFile($serviceConcretePath, $serviceContent, $force);

        return $results;
    }

    private static function buildUses(array $uses): string
    {
        $uses = array_values(array_unique($uses));

        return 'use ' . implode(";\nuse ", $uses) . ';';
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
