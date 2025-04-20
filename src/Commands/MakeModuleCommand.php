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

class MakeModuleCommand extends Command
{
    protected \$signature = 'make:module {name}
                            {--with-controller= : Optional subfolder for controller}
                            {--api : Generate an API Resource Controller}
                            {--with-form-requests : Generate Store/Update FormRequests}';

    protected \$description = 'Generate Repository, Service, Interfaces, DTO, Provider, Test, Controller and FormRequest for a module';

    public function handle(): void
    {
        \$name = Str::studly(\$this->argument('name'));

        RepositoryGenerator::generate(\$name);
        ServiceGenerator::generate(\$name);
        DTOGenerator::generate(\$name);
        ProviderGenerator::generate(\$name);
        TestGenerator::generate(\$name);

        if (\$this->option('with-controller') !== null) {
            ControllerGenerator::generate(\$name, \$this->option('with-controller'), \$this->option('api'));
        }

        if (\$this->option('with-form-requests')) {
            FormRequestGenerator::generate(\$name);
        }

        \$this->info("✅ Module {\$name} generated successfully.");
    }
}
