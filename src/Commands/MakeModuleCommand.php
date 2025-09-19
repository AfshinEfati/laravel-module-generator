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
                            {--c|controller= : Optional controller subfolder (e.g. Admin)}
                            {--a|api : Generate API style controller}
                            {--r|requests : Generate FormRequests (Store/Update)}
                            {--t|tests : Force generate feature tests}
                            {--nc|no-controller : Do not generate controller}
                            {--nr|no-resource : Do not generate API Resource}
                            {--nd|no-dto : Do not generate DTO}
                            {--nt|no-test : Do not generate feature test}
                            {--np|no-provider : Do not generate provider}
                            {--f|force : Overwrite existing files}';

    protected $description = 'Generate Repository, Service, DTO, Provider, Resource, Controller and (optionally) FormRequests for a module';

    public function handle(): int
    {
        $name          = Str::studly($this->argument('name'));
        $defaults      = (array) config('module-generator.defaults', []);
        $baseNamespace = (string) config('module-generator.base_namespace', 'App');

        $controllerSub = $this->option('controller');
        $isApi         = $this->input->hasParameterOption(['--api', '--a', '-a']);
        $force         = $this->input->hasParameterOption(['--force', '--f', '-f']);

        // toggles
        $withController = (bool) ($defaults['with_controller'] ?? true);
        if ($this->input->hasParameterOption(['--no-controller', '--nc', '-nc'])) {
            $withController = false;
        }
        if (is_string($controllerSub) && $controllerSub !== '') {
            $withController = true;
        }

        $withRequests = (bool) ($defaults['with_form_requests'] ?? false);
        if ($this->input->hasParameterOption(['--requests', '--r', '-r'])) {
            $withRequests = (bool) $this->option('requests');
        }

        $withUnitTest = (bool) ($defaults['with_unit_test'] ?? true);
        if ($this->input->hasParameterOption(['--no-test', '--nt', '-nt'])) {
            $withUnitTest = false;
        }
        if ($this->input->hasParameterOption(['--tests', '--t', '-t'])) { // force on
            $withUnitTest = true;
        }

        $withResource = (bool) ($defaults['with_resource'] ?? true);
        if ($this->input->hasParameterOption(['--no-resource', '--nr', '-nr'])) {
            $withResource = false;
        }

        $withDTO = (bool) ($defaults['with_dto'] ?? true);
        if ($this->input->hasParameterOption(['--no-dto', '--nd', '-nd'])) {
            $withDTO = false;
        }

        $withProvider = (bool) ($defaults['with_provider'] ?? true);
        if ($this->input->hasParameterOption(['--no-provider', '--np', '-np'])) {
            $withProvider = false;
        }

        // generate
        $repoResults = RepositoryGenerator::generate($name, $baseNamespace, $force);
        $this->reportResults('Repository', $repoResults);

        $serviceResults = ServiceGenerator::generate(
            name: $name,
            baseNamespace: $baseNamespace,
            usesDto: $withDTO,
            useInterfaces: $withProvider,
            force: $force
        );
        $this->reportResults('Service', $serviceResults);

        if ($withDTO) {
            $dtoResults = DTOGenerator::generate($name, $baseNamespace, $force);
            $this->reportResults('DTO', $dtoResults);
        }

        if ($withResource) {
            $resourceResults = ResourceGenerator::generate($name, $baseNamespace, $force);
            $this->reportResults('Resource', $resourceResults);
        }

        if ($withProvider) {
            $providerResults = ProviderGenerator::generateAndRegister($name, $baseNamespace, $force);
            $this->reportResults('Provider', $providerResults);
        } else {
            $this->warn('• Provider skipped. Remember to bind the repository/service manually.');
        }

        if ($withController) {
            $controllerResults = ControllerGenerator::generate(
                name: $name,
                baseNamespace: $baseNamespace,
                controllerSubfolder: is_string($controllerSub) ? $controllerSub : null,
                isApi: $isApi,
                withRequests: $withRequests,
                usesDto: $withDTO,
                usesResource: $withResource,
                force: $force
            );
            $this->reportResults('Controller', $controllerResults);
        } else {
            $this->line("• Controller skipped.");
        }

        if ($withRequests) {
            $requestResults = FormRequestGenerator::generate($name, $baseNamespace, $force);
            $this->reportResults('FormRequest', $requestResults);
        } else {
            $this->line("• FormRequests skipped.");
        }

        if ($withUnitTest) {
            $testResults = TestGenerator::generate(
                name: $name,
                baseNamespace: $baseNamespace,
                controllerSubfolder: is_string($controllerSub) ? $controllerSub : null,
                force: $force
            );
            $this->reportResults('Feature test', $testResults);
        } else {
            $this->line("• Tests skipped.");
        }

        $this->info("✅ Module {$name} generated successfully.");
        return self::SUCCESS;
    }

    private function reportResults(string $label, array $results): void
    {
        $created = [];
        $skipped = [];

        foreach ($results as $path => $written) {
            if ($written) {
                $created[] = $path;
            } else {
                $skipped[] = $path;
            }
        }

        if (!empty($created)) {
            $this->info(sprintf('• %s generated: %d file(s).', $label, count($created)));
        }

        foreach ($skipped as $path) {
            $this->line(sprintf('  - Skipped existing file: %s (use --force to overwrite)', $path));
        }
    }
}
