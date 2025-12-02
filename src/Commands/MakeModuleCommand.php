<?php

namespace Efati\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Generators\RepositoryGenerator;
use Efati\ModuleGenerator\Generators\ServiceGenerator;
use Efati\ModuleGenerator\Generators\DTOGenerator;
use Efati\ModuleGenerator\Generators\ProviderGenerator;
use Efati\ModuleGenerator\Generators\TestGenerator;
use Efati\ModuleGenerator\Generators\ControllerGenerator;
use Efati\ModuleGenerator\Generators\FormRequestGenerator;
use Efati\ModuleGenerator\Generators\ResourceGenerator;
use Efati\ModuleGenerator\Generators\ActionGenerator;
use Efati\ModuleGenerator\Generators\PolicyGenerator;
use Efati\ModuleGenerator\Generators\SwaggerDocGenerator;
use Efati\ModuleGenerator\Support\MigrationFieldParser;
use Efati\ModuleGenerator\Support\ModelInspector;
use Efati\ModuleGenerator\Support\SchemaParser;
use Efati\ModuleGenerator\Support\RuntimeFieldParser;
use Efati\ModuleGenerator\Support\RouteInspector;
use Efati\ModuleGenerator\Support\SwaggerPathGuesser;


class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
                            {name : The model/module base name (e.g. Product)}
                            {--c|controller= : Optional controller subfolder (e.g. Admin)}
                            {--api : Generate API style controller}
                            {--r|requests : Generate FormRequests (Store/Update)}
                            {--t|tests : Force generate feature tests}
                            {--nc|no-controller : Do not generate controller}
                            {--nr|no-resource : Do not generate API Resource}
                            {--nd|no-dto : Do not generate DTO}
                            {--nt|no-test : Do not generate feature test}
                            {--np|no-provider : Do not generate provider}
                            {--actions : Generate Actions for the module}
                            {--no-actions : Skip generating Actions}
                            {--policy : Generate Policy for the module}
                            {--no-policy : Skip generating Policy}
                            {--sg|swagger : Generate Swagger/OpenAPI annotations}
                            {--no-swagger : Skip generating Swagger/OpenAPI annotations}
                            {--a|all : Generate the full module stack (controllers, requests, resources, tests, provider, DTOs, swagger, actions)}
                            {--f|full : Generate the full module stack (controllers, requests, resources, tests, provider, DTOs, swagger, actions)}
                            {--fm|from-migration= : Migration file path or hint for inferring fields}
                            {--fields= : Inline schema definition for modules without migrations}
                            {--force : Overwrite existing files}';


    protected $description = 'Generate Repository, Service, DTO, Provider, Resource, Controller and (optionally) FormRequests for a module';

    public function handle(): int
    {
        $name          = Str::studly($this->argument('name'));
        $defaults      = (array) config('module-generator.defaults', []);
        $baseNamespace = (string) config('module-generator.base_namespace', 'App');
        $schemaDefinitions = $this->parseSchemaOption();

        $controllerSub = $this->option('controller');
        $defaultControllerType = (string) ($defaults['controller_type'] ?? 'web');
        $isApi         = $this->input->hasParameterOption(['--api']) || $defaultControllerType === 'api';
        $force         = (bool) $this->option('force');
        $fullStack     = (bool) $this->option('full');
        $allStack      = (bool) $this->option('all');
        if ($allStack) {
            $fullStack = true;
        }

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
        if ($isApi) {
            $withRequests = true;
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

        $withActions = (bool) ($defaults['with_actions'] ?? false);
        if ($this->input->hasParameterOption(['--actions'])) {
            $withActions = true;
        }
        if ($this->input->hasParameterOption(['--no-actions'])) {
            $withActions = false;
        }
        if ($isApi && !$this->input->hasParameterOption(['--no-actions'])) {
            $withActions = true;
        }

        $withPolicy = (bool) ($defaults['with_policy'] ?? false);
        if ($this->input->hasParameterOption(['--policy'])) {
            $withPolicy = true;
        }
        if ($this->input->hasParameterOption(['--no-policy'])) {
            $withPolicy = false;
        }

        $withSwagger = (bool) ($defaults['with_swagger'] ?? false);
        $swaggerOnly = false;
        if ($this->input->hasParameterOption(['--swagger', '--sg'])) {
            $withSwagger = true;
            // Check if --swagger is the only flag provided (besides name)
            $providedOptions = array_filter([
                $this->input->hasParameterOption(['--controller', '--c', '-c']),
                $this->input->hasParameterOption(['--api']),
                $this->input->hasParameterOption(['--all', '--a', '-a']),
                $this->input->hasParameterOption(['--full', '--f', '-f']),
                $this->input->hasParameterOption(['--requests', '--r', '-r']),
                $this->input->hasParameterOption(['--tests', '--t', '-t']),
                $this->input->hasParameterOption(['--no-controller', '--nc', '-nc']),
                $this->input->hasParameterOption(['--no-resource', '--nr', '-nr']),
                $this->input->hasParameterOption(['--no-dto', '--nd', '-nd']),
                $this->input->hasParameterOption(['--no-test', '--nt', '-nt']),
                $this->input->hasParameterOption(['--no-provider', '--np', '-np']),
                $this->input->hasParameterOption(['--actions']),
                $this->input->hasParameterOption(['--no-actions']),
                $this->input->hasParameterOption(['--from-migration', '--fm']),
                $this->input->hasParameterOption(['--fields']),
            ]);

            if (empty($providedOptions)) {
                $swaggerOnly = true;
                $withController = false;
                $withResource = false;
                $withDTO = false;
                $withProvider = false;
                $withActions = false;
                $withPolicy = false;
                $withUnitTest = false;
                $withRequests = false;
            }
        }
        if ($this->input->hasParameterOption(['--no-swagger'])) {
            $withSwagger = false;
        }
        if ($fullStack) {
            $withController = true;
            $withRequests = true;
            $withUnitTest = true;
            $withResource = true;
            $withDTO = true;
            $withProvider = true;
            $withActions = true;
            $withPolicy = true;
            $withSwagger = true;
            $swaggerOnly = false;
            $isApi = true;
        }
        if ($withSwagger && !$isApi && !$swaggerOnly) {
            $isApi = true;
            $this->warn('• --swagger implicitly enables API controllers.');
        }

        // Validate parsed fields before generation
        if (!empty($parsedFields)) {
            $this->validateParsedFields($parsedFields);
        }

        $modelFqcn       = $baseNamespace . '\\Models\\' . $name;
        $migrationHint   = $this->option('from-migration');
        $parsed          = null;
        $parsedFields    = [];
        $parsedRelations = [];
        $parsedTable     = null;

        $modelExists = class_exists($modelFqcn) && is_subclass_of($modelFqcn, EloquentModel::class);

        if (!$modelExists && empty($schemaDefinitions) && empty($migrationHint) && !$swaggerOnly) {
            $this->error("• Model {$modelFqcn} was not found. Create the model first or provide schema metadata via --fields/--from-migration.");
            return self::FAILURE;
        }

        $runtimeFields     = [];
        $runtimeRelations  = [];
        $migrationFields   = [];
        $migrationRelations = [];
        $fillableColumns   = [];
        $hasInlineSchema   = !empty($schemaDefinitions);

        if ($hasInlineSchema) {
            [$parsedFields, $parsedRelations] = $this->prepareSchemaDefinitions($schemaDefinitions);
            $parsedTable = Str::snake(Str::pluralStudly($name));
        }

        if ($modelExists) {
            $runtime = RuntimeFieldParser::parse($modelFqcn);
            if ($parsedTable === null && !empty($runtime['table'])) {
                $parsedTable = $runtime['table'];
            }

            $runtimeFields    = $runtime['fields'] ?? [];
            $runtimeRelations = $runtime['relations'] ?? [];

            if (empty($runtimeFields) && !$hasInlineSchema && (empty($migrationHint))) {
                $this->warn('• Unable to inspect database columns for the model. Falling back to migration parsing.');
            }

            $fillableColumns = ModelInspector::extractFillable($modelFqcn);
        }

        if (!$hasInlineSchema) {
            $parsed = null;
            if (is_string($migrationHint) && $migrationHint !== '') {
                $parsed = MigrationFieldParser::parse($name, $migrationHint);
            } else {
                $parsed = MigrationFieldParser::parse($name, null);
            }

            if (is_array($parsed)) {
                $migrationFields    = $parsed['fields'] ?? [];
                $migrationRelations = $parsed['relations'] ?? [];
                if ($parsedTable === null && !empty($parsed['table'])) {
                    $parsedTable = $parsed['table'];
                }
            }

            if (is_string($migrationHint) && $migrationHint !== '' && empty($migrationFields)) {
                $this->warn('• Unable to extract fields from the provided migration hint.');
            } elseif (!$modelExists && empty($migrationFields) && empty($runtimeFields)) {
                $this->warn('• Model class not found and fields could not be inferred from migration. Some generators may use empty metadata.');
            } elseif ($modelExists && empty($migrationFields) && empty($runtimeFields)) {
                $this->warn('• Unable to infer fields from database or migrations. Some generators may use empty metadata.');
            }
        }

        if ($hasInlineSchema) {
            // keep schema-provided metadata intact
        } else {
            $parsedFields = $this->mergeFieldDefinitions($runtimeFields, $migrationFields);
            $parsedRelations = $this->mergeRelationDefinitions($runtimeRelations, $migrationRelations);

            if (!empty($fillableColumns)) {
                $parsedFields = $this->alignFieldsToFillable($fillableColumns, $parsedFields, $migrationFields, $runtimeFields);
                $parsedRelations = $this->rebuildRelationsForFields($parsedFields, $parsedRelations, $migrationRelations, $runtimeRelations);
            }
        }

        if ($parsedTable === null && !$hasInlineSchema) {
            $parsedTable = Str::snake(Str::pluralStudly($name));
        }

        // Handle swagger-only generation
        if ($swaggerOnly) {
            if ($withSwagger) {
                $swaggerData = self::buildSwaggerData($name, null, $baseNamespace, []);
                if ($swaggerData !== null) {
                    SwaggerDocGenerator::generate($name, $baseNamespace, $swaggerData, $parsedFields, $force);
                    $this->info("✅ Swagger documentation for {$name} generated successfully.");
                } else {
                    $this->warn('• Swagger documentation could not be generated.');
                }
            }
            return self::SUCCESS;
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
            $dtoResults = DTOGenerator::generate($name, $baseNamespace, $force, $parsedFields);

            $this->reportResults('DTO', $dtoResults);
        }

        if ($withResource) {
            $resourceResults = ResourceGenerator::generate(
                $name,
                $baseNamespace,
                $force,
                $parsedFields,
                $parsedRelations
            );

            $this->reportResults('Resource', $resourceResults);
        }

        if ($withActions) {
            $actionResults = ActionGenerator::generate(
                name: $name,
                baseNamespace: $baseNamespace,
                usesDto: $withDTO,
                force: $force
            );

            $this->reportResults('Action', $actionResults);
        }

        if ($withPolicy) {
            $policyResults = PolicyGenerator::generate($name, $baseNamespace, $force);
            $this->reportResults('Policy', $policyResults);
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
                withSwagger: $withSwagger,
                force: $force,
                withActions: $withActions,
                fields: $parsedFields
            );
            $this->reportResults('Controller', $controllerResults);
        } else {
            $this->line("• Controller skipped.");
        }

        if ($withRequests) {
            $requestResults = FormRequestGenerator::generate(
                $name,
                $baseNamespace,
                $force,
                $parsedFields,
                $parsedTable
            );

            $this->reportResults('FormRequest', $requestResults);
        } else {
            $this->line("• FormRequests skipped.");
        }

        if ($withUnitTest) {
            $testResults = TestGenerator::generate(
                name: $name,
                baseNamespace: $baseNamespace,
                controllerSubfolder: is_string($controllerSub) ? $controllerSub : null,
                force: $force,
                fields: $parsedFields

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

    private function mergeFieldDefinitions(array $primary, array $secondary): array
    {
        if (empty($primary)) {
            $primary = [];
        }

        foreach ($secondary as $name => $meta) {
            if (!is_string($name) || $name === '') {
                continue;
            }

            $existing = $primary[$name] ?? [];
            $primary[$name] = array_merge($existing, $meta);
            $primary[$name]['name'] = $name;
        }

        return $primary;
    }

    private function mergeRelationDefinitions(array $primary, array $secondary): array
    {
        if (empty($primary)) {
            $primary = [];
        }

        foreach ($secondary as $name => $meta) {
            if (!is_string($name) || $name === '') {
                continue;
            }
            $primary[$name] = array_merge($primary[$name] ?? [], $meta);
            $primary[$name]['name'] = $meta['name'] ?? $name;
        }

        return $primary;
    }

    private function alignFieldsToFillable(array $fillable, array $fields, array $migrationFields, array $runtimeFields): array
    {
        if (empty($fillable)) {
            return $fields;
        }

        $aligned = [];

        foreach ($fillable as $column) {
            if (!is_string($column) || $column === '') {
                continue;
            }

            $meta = $fields[$column] ?? $migrationFields[$column] ?? $runtimeFields[$column] ?? null;

            if ($meta === null) {
                $meta = $this->defaultFieldMetadata($column);
            } else {
                $meta['name'] = $column;
            }

            $aligned[$column] = $meta;
        }

        return $aligned;
    }

    private function rebuildRelationsForFields(array $fields, array $existingRelations, array $migrationRelations, array $runtimeRelations): array
    {
        $lookup = [];

        foreach ([$existingRelations, $migrationRelations, $runtimeRelations] as $relationSet) {
            foreach ($relationSet as $key => $meta) {
                if (is_string($key) && $key !== '' && !isset($lookup[$key])) {
                    $lookup[$key] = $meta;
                }
            }
        }

        $relations = [];

        foreach ($fields as $name => $meta) {
            if (!is_array($meta)) {
                continue;
            }

            $foreign = $meta['foreign'] ?? null;
            if (!is_array($foreign)) {
                continue;
            }

            $relationName = $foreign['relation'] ?? null;

            if (!is_string($relationName) || $relationName === '') {
                if (Str::endsWith($name, '_id')) {
                    $relationName = Str::camel(substr($name, 0, -3));
                }
            }

            if (!is_string($relationName) || $relationName === '') {
                continue;
            }

            if (isset($lookup[$relationName])) {
                $relations[$relationName] = array_merge($lookup[$relationName], [
                    'name' => $lookup[$relationName]['name'] ?? $relationName,
                ]);
                continue;
            }

            $relations[$relationName] = [
                'name'          => $relationName,
                'type'          => $foreign['type'] ?? 'belongsTo',
                'foreign_key'   => $foreign['foreign_key'] ?? $name,
                'table'         => $foreign['table'] ?? null,
                'references'    => $foreign['references'] ?? 'id',
                'related_model' => $foreign['related_model'] ?? $foreign['related'] ?? $this->guessRelatedModel($name),
            ];
        }

        return $relations;
    }

    private function validateParsedFields(array $fields): void
    {
        $reserved = [
            'function',
            'class',
            'interface',
            'trait',
            'const',
            'public',
            'private',
            'protected',
            'static',
            'abstract',
            'final',
            'namespace',
            'use',
            'return',
            'echo',
            'print',
            'array',
            'string',
            'int',
            'float',
            'bool',
            'null',
            'true',
            'false',
            'new',
            'clone',
            'instanceof',
            'extends',
            'implements'
        ];

        $fieldNames = [];

        foreach ($fields as $name => $meta) {
            if (!is_string($name) || $name === '') {
                $this->warn("• Skipping invalid field name: empty or non-string");
                continue;
            }

            // Check for reserved keywords
            if (in_array(strtolower($name), $reserved, true)) {
                $this->error("• Field name '{$name}' is a reserved PHP keyword and cannot be used.");
                continue;
            }

            // Check for valid identifier
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
                $this->error("• Field name '{$name}' is not a valid PHP identifier.");
                continue;
            }

            // Check for duplicates
            if (in_array($name, $fieldNames, true)) {
                $this->warn("• Duplicate field name detected: '{$name}' (will be skipped)");
                continue;
            }

            $fieldNames[] = $name;
        }
    }

    private function defaultFieldMetadata(string $name): array
    {
        return [
            'name'         => $name,
            'method'       => 'inferred',
            'type'         => 'string',
            'cast'         => null,
            'length'       => null,
            'scale'        => null,
            'nullable'     => false,
            'unique'       => false,
            'default'      => null,
            'enum'         => null,
            'auto_managed' => false,
            'foreign'      => null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $foreign
     * @return array{type: string, references: string, table: string|null, related: string, relation: string}|null
     */
    private function buildForeignMetadataFromSchema(string $field, ?array $foreign): ?array
    {
        $hasForeignInfo      = is_array($foreign) && (!empty($foreign['table']) || !empty($foreign['column']));
        $looksLikeForeignKey = Str::endsWith($field, '_id');

        if (!$hasForeignInfo && !$looksLikeForeignKey) {
            return null;
        }

        $base = $looksLikeForeignKey ? substr($field, 0, -3) : $field;
        $base = Str::singular($base) ?: $field;

        $relation = Str::camel($base);
        $related  = Str::studly($base);

        $table   = null;
        $column  = 'id';

        if (is_array($foreign)) {
            if (isset($foreign['table']) && is_string($foreign['table']) && $foreign['table'] !== '') {
                $table = $foreign['table'];
            }
            if (isset($foreign['column']) && is_string($foreign['column']) && $foreign['column'] !== '') {
                $column = $foreign['column'];
            }
        }

        if ($table === null || $table === '') {
            $table = Str::snake(Str::pluralStudly($related));
        }

        $validRelationTypes = ['belongsTo', 'hasOne', 'hasMany', 'belongsToMany'];

        if (is_array($foreign) && isset($foreign['type']) && !in_array($foreign['type'], $validRelationTypes, true)) {
            $this->warn("• Invalid relationship type '{$foreign['type']}' for field '{$field}'. Using 'belongsTo' as default.");
        }

        return [
            'type'       => $foreign['type'] ?? 'belongsTo',
            'references' => $column,
            'table'      => $table,
            'related'    => $related,
            'relation'   => $relation,
        ];
    }

    private function inferCastFromType(string $type): ?string
    {
        return match ($type) {
            'integer' => 'int',
            'numeric' => 'float',
            'float' => 'float',
            'decimal' => 'float',
            'boolean' => 'bool',
            'json', 'array' => 'array',
            'date' => 'date',
            'datetime' => 'datetime',
            default => null,
        };
    }

    /**
     * Parse the --fields option into an array of field definitions.
     */
    private function parseSchemaOption(): array
    {
        $raw = $this->option('fields');

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        return SchemaParser::parse($raw);
    }

    /**
     * Build swagger data for standalone swagger generation.
     */
    private static function buildSwaggerData(string $name, ?string $controllerSubfolder, string $baseNamespace, array $controllerMiddleware): ?array
    {
        $tag       = Str::studly($name);
        $paramName = Str::camel($name);

        $slugHints          = SwaggerPathGuesser::slugHints($name);
        $controllerSegments = SwaggerPathGuesser::controllerSegments($controllerSubfolder);
        $resourceSegment    = SwaggerPathGuesser::defaultResourceSegment($name);
        $basePath           = SwaggerPathGuesser::assemblePath($controllerSegments, $resourceSegment);

        $routeMap = RouteInspector::discoverResourceUris(Str::studly($name) . 'Controller', $slugHints);
        if (isset($routeMap['index'])) {
            $basePath = RouteInspector::pathFromEntry($routeMap['index']) ?? $basePath;
        }

        $detectedParam = RouteInspector::extractParamName($routeMap['show'] ?? ($routeMap['update'] ?? ($routeMap['destroy'] ?? null)));
        if ($detectedParam) {
            $paramName = $detectedParam;
        }

        $indexEntry  = $routeMap['index'] ?? null;
        $storeEntry  = $routeMap['store'] ?? null;
        $showEntry   = $routeMap['show'] ?? null;
        $updateEntry = $routeMap['update'] ?? null;
        $destroyEntry = $routeMap['destroy'] ?? null;

        $basePathNormalized = RouteInspector::normalizeUri($basePath);
        $indexPath   = RouteInspector::pathFromEntry($indexEntry) ?? $basePathNormalized;
        $storePath   = RouteInspector::pathFromEntry($storeEntry) ?? $indexPath;
        $itemPath    = RouteInspector::pathFromEntry($showEntry) ?? null;
        $updatePath  = RouteInspector::pathFromEntry($updateEntry) ?? null;
        $destroyPath = RouteInspector::pathFromEntry($destroyEntry) ?? null;

        $fallbackItemPath = self::fallbackItemPath($indexPath, $paramName);
        $itemPath    = $itemPath ?? $fallbackItemPath;
        $updatePath  = $updatePath ?? $itemPath;
        $destroyPath = $destroyPath ?? $itemPath;

        $securityConfig = (array) config('module-generator.swagger.security', []);
        $configuredSchemes = (array) ($securityConfig['schemes'] ?? []);
        $defaultScheme = (string) ($securityConfig['default'] ?? array_key_first($configuredSchemes) ?? 'bearerAuth');
        $authMiddleware = array_map('strtolower', array_filter((array) ($securityConfig['auth_middleware'] ?? ['auth', 'auth:api', 'auth:sanctum'])));
        $normalizedControllerMiddleware = array_map('strtolower', array_map('trim', $controllerMiddleware));
        $securitySchemeExists = $defaultScheme !== '' && array_key_exists($defaultScheme, $configuredSchemes);

        $indexMiddleware   = RouteInspector::middlewareFromEntry($indexEntry);
        $storeMiddleware   = RouteInspector::middlewareFromEntry($storeEntry);
        $showMiddleware    = RouteInspector::middlewareFromEntry($showEntry);
        $updateMiddleware  = RouteInspector::middlewareFromEntry($updateEntry);
        $destroyMiddleware = RouteInspector::middlewareFromEntry($destroyEntry);

        $requiresAuthFor = static function (array $routeMiddleware) use ($normalizedControllerMiddleware, $authMiddleware): bool {
            $combined = array_values(array_unique(array_merge($normalizedControllerMiddleware, $routeMiddleware)));
            return !empty(array_intersect($combined, $authMiddleware));
        };

        $indexRequiresAuth   = $requiresAuthFor($indexMiddleware);
        $storeRequiresAuth   = $requiresAuthFor($storeMiddleware);
        $showRequiresAuth    = $requiresAuthFor($showMiddleware);
        $updateRequiresAuth  = $requiresAuthFor($updateMiddleware);
        $destroyRequiresAuth = $requiresAuthFor($destroyMiddleware);

        $indexSecurity   = ($securitySchemeExists && $indexRequiresAuth) ? [$defaultScheme] : [];
        $storeSecurity   = ($securitySchemeExists && $storeRequiresAuth) ? [$defaultScheme] : [];
        $showSecurity    = ($securitySchemeExists && $showRequiresAuth) ? [$defaultScheme] : [];
        $updateSecurity  = ($securitySchemeExists && $updateRequiresAuth) ? [$defaultScheme] : [];
        $destroySecurity = ($securitySchemeExists && $destroyRequiresAuth) ? [$defaultScheme] : [];

        $securityEnabled = $securitySchemeExists && (
            $indexRequiresAuth ||
            $storeRequiresAuth ||
            $showRequiresAuth ||
            $updateRequiresAuth ||
            $destroyRequiresAuth
        );

        $operations = [
            [
                'name'        => 'index',
                'httpMethod'  => 'Get',
                'path'        => $indexPath,
                'summary'     => "List {$tag}",
                'requestBody' => false,
                'pathParam'   => false,
                'responses'   => [
                    ['code' => 200, 'description' => 'Successful response'],
                    ['code' => 401, 'description' => 'Unauthenticated'],
                ],
                'security'    => $indexSecurity,
            ],
            [
                'name'        => 'store',
                'httpMethod'  => 'Post',
                'path'        => $storePath,
                'summary'     => "Create {$tag}",
                'requestBody' => true,
                'pathParam'   => false,
                'responses'   => [
                    ['code' => 201, 'description' => 'Created'],
                    ['code' => 401, 'description' => 'Unauthenticated'],
                    ['code' => 422, 'description' => 'Validation error'],
                ],
                'security'    => $storeSecurity,
            ],
            [
                'name'        => 'show',
                'httpMethod'  => 'Get',
                'path'        => $itemPath,
                'summary'     => "Show {$tag}",
                'requestBody' => false,
                'pathParam'   => true,
                'responses'   => [
                    ['code' => 200, 'description' => 'Successful response'],
                    ['code' => 401, 'description' => 'Unauthenticated'],
                    ['code' => 404, 'description' => 'Not found'],
                ],
                'security'    => $showSecurity,
            ],
            [
                'name'        => 'update',
                'httpMethod'  => 'Put',
                'path'        => $updatePath,
                'summary'     => "Update {$tag}",
                'requestBody' => true,
                'pathParam'   => true,
                'responses'   => [
                    ['code' => 200, 'description' => 'Updated'],
                    ['code' => 401, 'description' => 'Unauthenticated'],
                    ['code' => 422, 'description' => 'Validation error'],
                    ['code' => 404, 'description' => 'Not found'],
                ],
                'security'    => $updateSecurity,
            ],
            [
                'name'        => 'destroy',
                'httpMethod'  => 'Delete',
                'path'        => $destroyPath,
                'summary'     => "Delete {$tag}",
                'requestBody' => false,
                'pathParam'   => true,
                'responses'   => [
                    ['code' => 204, 'description' => 'Deleted'],
                    ['code' => 401, 'description' => 'Unauthenticated'],
                    ['code' => 404, 'description' => 'Not found'],
                ],
                'security'    => $destroySecurity,
            ],
        ];

        return [
            'tag'        => $tag,
            'param_name' => $paramName,
            'operations' => $operations,
            'base_path'  => $basePathNormalized,
            'namespace'  => $baseNamespace,
            'security'   => [
                'enabled' => $securityEnabled,
                'default' => $defaultScheme,
                'schemes' => $securityEnabled ? $configuredSchemes : [],
            ],
        ];
    }

    private static function fallbackItemPath(string $basePath, string $paramName): string
    {
        $normalized = RouteInspector::normalizeUri($basePath);
        $normalized = rtrim($normalized, '/');

        if ($normalized === '' || $normalized === '/') {
            return '/{' . $paramName . '}';
        }

        return $normalized . '/{' . $paramName . '}';
    }
}
