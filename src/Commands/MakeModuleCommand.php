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
    protected $signature = 'make:module
                            {name : The model/module base name (e.g. Product)}
                            {--controller= : Optional controller subfolder (e.g. Admin)}
                            {--api : Mark generated controller for API style (passed to generator)}
                            {--requests : Generate FormRequests (Store/Update)}
                            {--no-controller : Do not generate controller}
                            {--no-resource : Do not generate API Resource}
                            {--no-dto : Do not generate DTO}
                            {--no-test : Do not generate feature test}
                            {--no-provider : Do not generate provider}';

    protected $description = 'Generate Repository, Service, DTO, Provider, Resource, Controller and (optionally) FormRequests for a module';

    public function handle(): int
    {
        $name          = Str::studly($this->argument('name'));
        $defaults      = (array) config('module-generator.defaults', []);
        $baseNamespace = (string) config('module-generator.base_namespace', 'App');

        $controllerSub = $this->option('controller');
        $isApi         = (bool) $this->option('api');

        // ---- Toggle resolution (fix) ----
        $withController = (bool) ($defaults['with_controller'] ?? true);
        if ($this->option('no-controller')) {
            $withController = false;
        }
        // Force controller on if user asked for a controller subfolder explicitly
        if (is_string($controllerSub) && $controllerSub !== '') {
            $withController = true;
        }

        $withRequests = $this->option('requests');
        if ($withRequests === null) {
            $withRequests = (bool) ($defaults['with_form_requests'] ?? false);
        } else {
            $withRequests = (bool) $withRequests;
        }

        $withUnitTest = (bool) ($defaults['with_unit_test'] ?? true);
        if ($this->option('no-test')) {
            $withUnitTest = false;
        }

        $withResource = (bool) ($defaults['with_resource'] ?? true);
        if ($this->option('no-resource')) {
            $withResource = false;
        }

        $withDTO = (bool) ($defaults['with_dto'] ?? true);
        if ($this->option('no-dto')) {
            $withDTO = false;
        }

        $withProvider = (bool) ($defaults['with_provider'] ?? true);
        if ($this->option('no-provider')) {
            $withProvider = false;
        }
        // ---- end toggles ----

        // Always: Repository + Service
        RepositoryGenerator::generate($name, $baseNamespace);
        ServiceGenerator::generate($name, $baseNamespace);

        if ($withDTO) {
            DTOGenerator::generate($name, $baseNamespace);
        }

        if ($withResource) {
            ResourceGenerator::generate($name, $baseNamespace);
        }

        if ($withProvider) {
            ProviderGenerator::generateAndRegister($name, $baseNamespace);
        }

        if ($withController) {
            ControllerGenerator::generate(
                name: $name,
                baseNamespace: $baseNamespace,
                controllerSubfolder: is_string($controllerSub) ? $controllerSub : null,
                isApi: $isApi,
                withRequests: (bool) $withRequests
            );
            $this->info("• Controller generated.");
        } else {
            $this->line("• Controller skipped (use --controller=Subfolder or remove --no-controller).");
        }

        if ($withRequests) {
            FormRequestGenerator::generate($name, $baseNamespace);
            $this->info("• FormRequests generated.");
        } else {
            $this->line("• FormRequests skipped (use --requests to include).");
        }

        if ($withUnitTest) {
            TestGenerator::generate($name);
            $this->info("• Feature test skeleton generated.");
        } else {
            $this->line("• Tests skipped.");
        }

        $this->info("✅ Module {$name} generated successfully.");
        return self::SUCCESS;
    }
}
