<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MakeFieldtypeTest extends TestCase
{
    use Concerns\CleansUpGeneratedPaths,
        Concerns\FakesComposerInstalls;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        Process::fake();
        $this->files = app(Filesystem::class);
        $this->files->makeDirectory(__DIR__.'/../../../resources/dist-dev', 0755, true, true);
        $this->fakeSuccessfulComposerRequire();
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    #[Test]
    public function it_can_generate_a_fieldtype()
    {
        $this->assertFileDoesNotExist(base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/KnightRider.vue'));

        $this
            ->artisan('statamic:make:fieldtype', ['name' => 'KnightRider'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->assertFileExists($fieldtype = base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertStringContainsString('namespace App\Fieldtypes;', $this->files->get($fieldtype));

        $this->assertFileExists(resource_path('js/components/fieldtypes/KnightRider.vue'));
    }

    #[Test]
    public function it_can_generate_a_fieldtype_without_a_vue_component()
    {
        $this->assertFileDoesNotExist(base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/KnightRider.vue'));

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider', '--php' => true]);

        $this->assertFileExists($fieldtype = base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertStringContainsString('namespace App\Fieldtypes;', $this->files->get($fieldtype));

        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/KnightRider.vue'));
    }

    #[Test]
    public function it_can_generate_a_fieldtype_and_run_setup_cp_vite()
    {
        $this->assertFileDoesNotExist(base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/KnightRider.vue'));

        $this->files->put(app_path('Providers/AppServiceProvider.php'), <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
PHP
        );

        $this->files->put(base_path('package.json'), json_encode([]));

        $this
            ->artisan('statamic:make:fieldtype', ['name' => 'KnightRider'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", true);

        Process::assertRan('npm install');

        $this->assertFileExists($fieldtype = base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertStringContainsString('namespace App\Fieldtypes;', $this->files->get($fieldtype));

        $this->assertFileExists(base_path('vite-cp.config.js'));
        $this->assertFileExists(resource_path('js/cp.js'));
        $this->assertFileExists(resource_path('js/components/fieldtypes/KnightRider.vue'));
    }

    #[Test]
    public function it_will_not_overwrite_an_existing_fieldtype()
    {
        $path = base_path('app/Fieldtypes/KnightRider.php');

        $this->assertFileDoesNotExist($path);

        $this
            ->artisan('statamic:make:fieldtype', ['name' => 'KnightRider'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->files->put($path, 'overwritten fieldtype');

        $this->assertStringContainsString('overwritten fieldtype', $this->files->get($path));

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider']);

        $this->assertStringContainsString('overwritten fieldtype', $this->files->get($path));
    }

    #[Test]
    public function using_force_option_will_overwrite_original_fieldtype()
    {
        $path = base_path('app/Fieldtypes/KnightRider.php');

        $this
            ->artisan('statamic:make:fieldtype', ['name' => 'KnightRider'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->files->put($path, 'overwritten fieldtype');

        $this->assertStringContainsString('overwritten fieldtype', $this->files->get($path));

        $this
            ->artisan('statamic:make:fieldtype', ['name' => 'KnightRider', '--force' => true])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->assertStringNotContainsString('overwritten fieldtype', $this->files->get($path));
    }

    #[Test]
    public function it_can_make_a_fieldtype_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->assertDirectoryDoesNotExist(public_path('vendor/statamic/cp-dev'));

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($fieldtype = "$path/src/Fieldtypes/Yoda.php");

        $this->artisan('statamic:make:fieldtype', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        Process::assertRan('npm install');

        $this->assertFileExists($fieldtype);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Fieldtypes;', $this->files->get($fieldtype));

        $this->assertDirectoryExists(public_path('vendor/statamic/cp-dev'));
    }
}
