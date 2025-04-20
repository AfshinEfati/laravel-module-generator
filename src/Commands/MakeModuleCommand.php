<?php

namespace Efati\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Generate Repository, Service, Interfaces, DTO, Provider and Test class for a module';

    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));

        $this->createRepositoryInterface($name);
        $this->createRepository($name);
        $this->createServiceInterface($name);
        $this->createService($name);
        $this->createServiceProvider($name);
        $this->createTest($name);
        $this->createDTO($name);

        $this->info("✅ {$name} module structure generated successfully.");
    }

    protected function createRepositoryInterface(string $name): void
    {
        $path = app_path("Repositories/{$name}/{$name}RepositoryInterface.php");

        File::ensureDirectoryExists(dirname($path));
        File::put($path, <<<PHP
<?php

namespace App\Repositories\\{$name};

use App\Repositories\Contracts\BaseRepositoryInterface;

interface {$name}RepositoryInterface extends BaseRepositoryInterface
{
    //
}
PHP);
    }

    protected function createRepository(string $name): void
    {
        $path = app_path("Repositories/{$name}/{$name}Repository.php");

        File::put($path, <<<PHP
<?php

namespace App\Repositories\\{$name};

use App\Models\\{$name};
use App\Repositories\Eloquent\BaseRepository;

class {$name}Repository extends BaseRepository implements {$name}RepositoryInterface
{
    public function __construct({$name} \$model)
    {
        parent::__construct(\$model);
    }

    // Add module-specific methods here
}
PHP);
    }

    protected function createServiceInterface(string $name): void
    {
        $path = app_path("Services/{$name}/{$name}ServiceInterface.php");

        File::ensureDirectoryExists(dirname($path));
        File::put($path, <<<PHP
<?php

namespace App\Services\\{$name};

use App\Services\Contracts\BaseServiceInterface;

interface {$name}ServiceInterface extends BaseServiceInterface
{
    //
}
PHP);
    }

    protected function createService(string $name): void
    {
        $path = app_path("Services/{$name}/{$name}Service.php");

        File::put($path, <<<PHP
<?php

namespace App\Services\\{$name};

use App\Services\BaseService;
use App\Repositories\\{$name}\\{$name}RepositoryInterface;

class {$name}Service extends BaseService implements {$name}ServiceInterface
{
    public function __construct(protected {$name}RepositoryInterface \$repository)
    {
        parent::__construct(\$repository);
    }

    // Add module-specific service logic here
}
PHP);
    }

    protected function createServiceProvider(string $name): void
    {
        $providerClass = "{$name}ServiceProvider";
        $path = app_path("Providers/{$providerClass}.php");

        File::put($path, <<<PHP
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\\{$name}\\{$name}RepositoryInterface;
use App\Repositories\\{$name}\\{$name}Repository;
use App\Services\\{$name}\\{$name}ServiceInterface;
use App\Services\\{$name}\\{$name}Service;

class {$providerClass} extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind({$name}RepositoryInterface::class, {$name}Repository::class);
        \$this->app->bind({$name}ServiceInterface::class, {$name}Service::class);
    }
}
PHP);
    }

    protected function createTest(string $name): void
    {
        $path = base_path("tests/Feature/{$name}ServiceTest.php");

        File::ensureDirectoryExists(dirname($path));
        File::put($path, <<<PHP
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\\{$name};
use Illuminate\Foundation\Testing\RefreshDatabase;

class {$name}ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_{$this->snake($name)}(): void
    {
        // \$data = [...];
        // \$this->postJson(route('{$this->snake($name)}.store'), \$data)->assertCreated();
        \$this->assertTrue(true);
    }

    public function test_update_{$this->snake($name)}(): void
    {
        // \$model = {$name}::factory()->create();
        // \$this->putJson(route('{$this->snake($name)}.update', \$model->id), [...])->assertOk();
        \$this->assertTrue(true);
    }

    public function test_find_{$this->snake($name)}(): void
    {
        // \$model = {$name}::factory()->create();
        // \$this->getJson(route('{$this->snake($name)}.show', \$model->id))->assertOk();
        \$this->assertTrue(true);
    }

    public function test_delete_{$this->snake($name)}(): void
    {
        // \$model = {$name}::factory()->create();
        // \$this->deleteJson(route('{$this->snake($name)}.destroy', \$model->id))->assertNoContent();
        \$this->assertTrue(true);
    }
}
PHP);
    }

    protected function createDTO(string $name): void
    {
        $modelClass = "App\\Models\\{$name}";
        $fillable = [];

        if (class_exists($modelClass)) {
            $model = new $modelClass;
            $fillable = $model->getFillable();
        }

        $props = implode("\n", array_map(fn($f) => "    public mixed \$$f;", $fillable));

        $constructorParams = implode(",\n", array_map(fn($f) => "        mixed \$$f", $fillable));

        $constructorBody = implode("\n", array_map(fn($f) => "        \$this->$f = \$$f;", $fillable));

        $assignments = implode(",\n", array_map(fn($f) => "            $f: \$request->input('$f')", $fillable));

        $path = app_path("DTOs/{$name}DTO.php");
        File::ensureDirectoryExists(dirname($path));
        File::put($path, <<<PHP
<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class {$name}DTO
{
$props

    public function __construct(
$constructorParams
    ) {
$constructorBody
    }

    public static function fromRequest(Request \$request): self
    {
        return new self(
$assignments
        );
    }
}
PHP);
    }



    protected function snake(string $name): string
    {
        return Str::snake($name);
    }
}
