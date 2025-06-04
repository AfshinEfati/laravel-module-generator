<?php

namespace Efati\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Generators\RepositoryGenerator;
use Efati\ModuleGenerator\Generators\ServiceGenerator;
use Efati\ModuleGenerator\Generators\DTOGenerator;
use Efati\ModuleGenerator\Generators\ProviderGenerator;
use Efati\ModuleGenerator\Generators\TestGenerator;
use Efati\ModuleGenerator\Generators\ControllerGenerator;
use Efati\ModuleGenerator\Generators\FormRequestGenerator;
use Efati\ModuleGenerator\Generators\ResourceGenerator;

class MakeModuleCommand extends Command
{
    protected $signature = <<<SIGNATURE
make:module {name}
            {--controller= : Generate controller (optional subfolder like Admin)}
            {--api : Generate an API Resource Controller}
            {--requests : Generate Store/Update FormRequests}
SIGNATURE;

    protected $description = 'Generate Repository, Service, Interfaces, DTO, Provider, Test, Controller and FormRequest for a module';

    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));

        // Core module generation
        RepositoryGenerator::generate($name);
        ServiceGenerator::generate($name);
        DTOGenerator::generate($name);
        ProviderGenerator::generate($name);
        TestGenerator::generate($name);
        ResourceGenerator::generate($name);

        // Controller generation
        $controllerOption = $this->option('controller');
        $controllerNamespace = is_string($controllerOption) ? $controllerOption : null;

        if ($controllerOption !== null) {
            ControllerGenerator::generate($name, $controllerNamespace, $this->option('api'), $this->option('requests'));
        }

        // FormRequest generation
        if ($this->option('requests')) {
            FormRequestGenerator::generate($name);
        }

        $this->info("✅ Module {$name} generated successfully.");
    }
}
