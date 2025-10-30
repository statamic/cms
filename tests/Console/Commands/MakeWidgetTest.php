<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MakeWidgetTest extends TestCase
{
    use Concerns\CleansUpGeneratedPaths,
        Concerns\FakesComposerInstalls;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        Process::fake();
        $this->files = app(Filesystem::class);
        $this->fakeSuccessfulComposerRequire();
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    #[Test]
    public function it_can_make_a_widget()
    {
        $this->assertFileDoesNotExist($widget = base_path('app/Widgets/Sloth.php'));
        $this->assertFileDoesNotExist($bladeView = resource_path('views/widgets/sloth.blade.php'));
        $this->assertFileDoesNotExist($vueComponent = resource_path('js/components/widgets/Sloth.vue'));

        $this
            ->artisan('statamic:make:widget', ['name' => 'Sloth'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->assertFileExists($widget);
        $this->assertStringContainsString('namespace App\Widgets;', $this->files->get($widget));
        $this->assertStringContainsString('return VueComponent::render(', $this->files->get($widget));

        $this->assertFileDoesNotExist($bladeView);

        $this->assertFileExists($vueComponent);
        $this->assertStringContainsString('<Widget title="Sloth">', $this->files->get($vueComponent));
    }

    #[Test]
    public function it_can_make_a_widget_with_a_blade_view()
    {
        $this->assertFileDoesNotExist($widget = base_path('app/Widgets/Sloth.php'));
        $this->assertFileDoesNotExist($bladeView = resource_path('views/widgets/sloth.blade.php'));
        $this->assertFileDoesNotExist($vueComponent = resource_path('js/components/widgets/Sloth.vue'));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth', '--blade' => true]);

        $this->assertFileExists($widget);
        $this->assertStringContainsString('namespace App\Widgets;', $this->files->get($widget));
        $this->assertStringContainsString('return view(', $this->files->get($widget));

        $this->assertFileExists($bladeView);
        $this->assertStringContainsString('<ui-widget title="Sloth">', $this->files->get($bladeView));

        $this->assertFileDoesNotExist($vueComponent);
    }

    #[Test]
    public function it_can_make_a_widget_and_run_setup_cp_vite()
    {
        $this->assertFileDoesNotExist($widget = base_path('app/Widgets/Sloth.php'));
        $this->assertFileDoesNotExist($vueComponent = resource_path('js/components/widgets/Sloth.vue'));

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
            ->artisan('statamic:make:widget', ['name' => 'Sloth'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", true);

        Process::assertRan('npm install');

        $this->assertFileExists($widget);
        $this->assertStringContainsString('namespace App\Widgets;', $this->files->get($widget));
        $this->assertStringContainsString('return VueComponent::render(', $this->files->get($widget));

        $this->assertFileExists($vueComponent);
        $this->assertStringContainsString('<Widget title="Sloth">', $this->files->get($vueComponent));

        $this->assertFileExists(base_path('vite-cp.config.js'));
        $this->assertFileExists(resource_path('js/cp.js'));
    }

    #[Test]
    public function it_will_not_overwrite_an_existing_widget()
    {
        $this->assertFileDoesNotExist($widget = base_path('app/Widgets/Sloth.php'));

        $this
            ->artisan('statamic:make:widget', ['name' => 'Sloth'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->files->put($widget, 'overwritten widget');

        $this->assertStringContainsString('overwritten widget', $this->files->get($widget));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertStringContainsString('overwritten widget', $this->files->get($widget));
    }

    #[Test]
    public function using_force_option_will_overwrite_original_widget()
    {
        $widget = base_path('app/Widgets/Sloth.php');

        $this
            ->artisan('statamic:make:widget', ['name' => 'Sloth'])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->files->put($widget, 'overwritten widget');

        $this->assertStringContainsString('overwritten widget', $this->files->get($widget));

        $this
            ->artisan('statamic:make:widget', ['name' => 'Sloth', '--force' => true])
            ->expectsQuestion("It doesn't look like Vite is setup for the Control Panel. Would you like to run `php please setup-cp-vite`?", false);

        $this->assertStringNotContainsString('overwritten widget', $this->files->get($widget));
    }

    #[Test]
    public function it_can_make_a_widget_into_an_addon()
    {
        $addon = base_path('addons/yoda/bag-odah');

        $this->assertFileDoesNotExist($widget = "$addon/src/Widgets/Yoda.php");
        $this->assertFileDoesNotExist($bladeView = "$addon/resources/views/widgets/yoda.blade.php");
        $this->assertFileDoesNotExist($vueComponent = "$addon/resources/js/components/widgets/Yoda.vue");

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($addon);

        $this->artisan('statamic:make:widget', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($widget);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Widgets;', $this->files->get($widget));

        $this->assertFileDoesNotExist($bladeView);

        $this->assertFileExists($vueComponent);
        $this->assertStringContainsString('<Widget title="Yoda">', $this->files->get($vueComponent));
    }
}
