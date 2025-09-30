<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\BaseClassLocator;
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
        $modelFqcn         = "{$baseNamespace}\\Models\\{$name}";

        $baseService = BaseClassLocator::baseService($baseNamespace);
        $baseServiceInterface = BaseClassLocator::baseServiceInterface($baseNamespace);

        $serviceInterfacePath = $contractPath . "/{$name}ServiceInterface.php";
        $serviceConcretePath  = $servicePath . "/{$name}Service.php";

        $results = [];

        // Interface generation
        $interfaceUses = [
            $modelFqcn,
            $baseServiceInterface['fqcn'],
        ];
        if ($usesDto) {
            $interfaceUses[] = $dtoFqcn;
        }

        $payloadDocType = $usesDto ? "{$name}DTO|array" : 'array';

        $interfaceStoreMethod = implode("\n", [
            '    /**',
            "     * @param {$payloadDocType} \$payload",
            "     * @return {$name}",
            '     */',
            "    public function store(mixed \$payload): {$name};",
        ]) . "\n";

        $interfaceUpdateMethod = implode("\n", [
            '    /**',
            '     * @param int|string $id',
            "     * @param {$payloadDocType} \$payload",
            '     */',
            '    public function update(int|string $id, mixed $payload): bool;',
        ]) . "\n";

        $interfaceContent = Stub::render('Service/interface', [
            'namespace'     => $baseNamespace . '\\Services\\Contracts',
            'uses'          => self::buildUses($interfaceUses),
            'interface'     => $name . 'ServiceInterface',
            'model'         => $name,
            'store_method'  => $interfaceStoreMethod,
            'update_method' => $interfaceUpdateMethod,
            'base_interface' => $baseServiceInterface['class'],
        ]);

        $results[$serviceInterfacePath] = self::writeFile($serviceInterfacePath, $interfaceContent, $force);

        // Service generation
        $serviceUses = [
            $baseNamespace . '\\Services\\Contracts\\' . $name . 'ServiceInterface',
            $baseService['fqcn'],
            $modelFqcn,
        ];

        $repositoryTypeHint = $useInterfaces ? $name . 'RepositoryInterface' : $name . 'Repository';
        $repositoryUse      = $useInterfaces ? $repoInterfaceFqcn : $repoConcreteFqcn;
        $serviceUses[]      = $repositoryUse;

        if ($usesDto) {
            $serviceUses[] = $dtoFqcn;
        }

        $serviceStoreMethodLines = [
            '    /**',
            "     * @param {$payloadDocType} \$payload",
            "     * @return {$name}",
            '     */',
            "    public function store(mixed \$payload): {$name}",
            '    {',
        ];
        if ($usesDto) {
            $serviceStoreMethodLines[] = "        if (\$payload instanceof {$name}DTO) {";
            $serviceStoreMethodLines[] = '            $payload = $payload->toArray();';
            $serviceStoreMethodLines[] = '        }';
            $serviceStoreMethodLines[] = '';
        }
        $serviceStoreMethodLines[] = "        /** @var {$name} */";
        $serviceStoreMethodLines[] = '        return parent::store($payload);';
        $serviceStoreMethodLines[] = '    }';
        $serviceStoreMethodLines[] = '';
        $serviceStoreMethod = implode("\n", $serviceStoreMethodLines);

        $serviceUpdateMethodLines = [
            '    /**',
            '     * @param int|string $id',
            "     * @param {$payloadDocType} \$payload",
            '     */',
            '    public function update(int|string $id, mixed $payload): bool',
            '    {',
        ];
        if ($usesDto) {
            $serviceUpdateMethodLines[] = "        if (\$payload instanceof {$name}DTO) {";
            $serviceUpdateMethodLines[] = '            $payload = $payload->toArray();';
            $serviceUpdateMethodLines[] = '        }';
            $serviceUpdateMethodLines[] = '';
        }
        $serviceUpdateMethodLines[] = '        return parent::update($id, $payload);';
        $serviceUpdateMethodLines[] = '    }';
        $serviceUpdateMethodLines[] = '';
        $serviceUpdateMethod = implode("\n", $serviceUpdateMethodLines);

        $serviceContent = Stub::render('Service/concrete', [
            'namespace'             => $baseNamespace . '\\Services',
            'uses'                  => self::buildUses($serviceUses),
            'class'                 => $name . 'Service',
            'interface'             => $name . 'ServiceInterface',
            'repository_type_hint'  => $repositoryTypeHint,
            'model'                 => $name,
            'store_method'          => $serviceStoreMethod,
            'update_method'         => $serviceUpdateMethod,
            'base_class'            => $baseService['class'],
        ]);

        $results[$serviceConcretePath] = self::writeFile($serviceConcretePath, $serviceContent, $force);

        return $results;
    }

    private static function buildUses(array $uses): string
    {
        $uses = array_values(array_unique(array_filter($uses)));

        return $uses ? 'use ' . implode(";\nuse ", $uses) . ';' : '';
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
