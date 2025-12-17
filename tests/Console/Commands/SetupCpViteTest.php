<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\Console\Commands\Concerns\CleansUpGeneratedPaths;
use Tests\TestCase;

class SetupCpViteTest extends TestCase
{
    use CleansUpGeneratedPaths;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app('files');

        $this->makeNecessaryFiles();

        Process::fake();
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    #[Test]
    public function it_installs_dependencies()
    {
        $this->files->put(base_path('package.json'), <<<'JSON'
{
    "dependencies": {
        "axios": "^1.4.0",
        "tailwindcss": "^4.0.6"
    },
    "devDependencies": {
        "postcss": "^8.4.24"
    }
}
JSON);

        $this
            ->artisan('statamic:setup-cp-vite')
            ->expectsOutputToContain('Installed dependencies');

        Process::assertRan('npm install');

        $this->assertStringContainsString(<<<'JSON'
    "dependencies": {
        "axios": "^1.4.0",
        "tailwindcss": "^4.0.6",
        "@statamic/cms": "file:./vendor/statamic/cms/resources/dist-package"
    },
    "devDependencies": {
        "postcss": "^8.4.24",
        "vite": "^7.0.4"
    }
JSON, $this->files->get(base_path('package.json')));
    }

    #[Test]
    public function it_adds_scripts_to_package_json()
    {
        $this->files->put(base_path('package.json'), <<<'JSON'
{
    "scripts": {
        "build": "vite build",
        "dev": "vite",
        "watch": "vite"
    }
}
JSON);

        $this
            ->artisan('statamic:setup-cp-vite')
            ->expectsOutputToContain('Added cp:dev and cp:build scripts to package.json');

        $this->assertStringContainsString(<<<'JSON'
    "scripts": {
        "build": "vite build",
        "dev": "vite",
        "watch": "vite",
        "cp:dev": "vite build --config vite-cp.config.js --watch",
        "cp:build": "vite build --config vite-cp.config.js"
    }
JSON, $this->files->get(base_path('package.json')));
    }

    #[Test]
    public function it_publishes_vite_config()
    {
        $this->assertFileDoesNotExist(base_path('vite-cp.config.js'));

        $this
            ->artisan('statamic:setup-cp-vite')
            ->expectsOutputToContain('Published vite-cp.config.js');

        $this->assertFileExists(base_path('vite-cp.config.js'));
    }

    #[Test]
    public function it_publishes_stubs()
    {
        $this->assertFileDoesNotExist(resource_path('css/cp.css'));
        $this->assertFileDoesNotExist(resource_path('js/cp.js'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/ExampleFieldtype.vue'));

        $this
            ->artisan('statamic:setup-cp-vite')
            ->expectsOutputToContain('Published stubs for Control Panel CSS & JavaScript.');

        $this->assertFileExists(resource_path('css/cp.css'));
        $this->assertFileExists(resource_path('js/cp.js'));
        $this->assertFileExists(resource_path('js/components/fieldtypes/ExampleFieldtype.vue'));
    }

    #[Test]
    public function it_only_publishes_necessary_stubs()
    {
        $this->assertFileDoesNotExist(resource_path('css/cp.css'));
        $this->assertFileDoesNotExist(resource_path('js/cp.js'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/ExampleFieldtype.vue'));

        $this
            ->artisan('statamic:setup-cp-vite', ['--only-necessary' => true])
            ->expectsOutputToContain('Published stubs for Control Panel CSS & JavaScript.');

        $this->assertFileExists(resource_path('css/cp.css'));
        $this->assertFileExists(resource_path('js/cp.js'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/ExampleFieldtype.vue'));
    }

    #[Test]
    public function it_publishes_dev_build()
    {
        $this->assertDirectoryDoesNotExist(public_path('vendor/statamic/cp-dev'));

        $this
            ->artisan('statamic:setup-cp-vite', ['--only-necessary' => true])
            ->expectsOutputToContain('Publishing [statamic-cp-dev] assets.');

        $this->assertDirectoryExists(public_path('vendor/statamic/cp-dev'));
    }

    #[Test]
    public function it_appends_vite_snippet_to_app_service_provider()
    {
        $this->assertStringNotContainsString("Statamic::vite('app', [", $this->files->get(app_path('Providers/AppServiceProvider.php')));

        $this
            ->artisan('statamic:setup-cp-vite')
            ->expectsOutputToContain('Added Statamic::vite() snippet to AppServiceProvider.');

        $this->assertStringContainsString(<<<'PHP'
    public function boot(): void
    {
        Statamic::vite('app', [
            'input' => [
                'resources/js/cp.js',
                'resources/css/cp.css',
            ],
            'buildDirectory' => 'vendor/app',
        ]);

        //
    }
PHP, $this->files->get(app_path('Providers/AppServiceProvider.php')));
    }

    private function makeNecessaryFiles(): void
    {
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
PHP);

        $this->files->put(base_path('package.json'), json_encode([]));

        $this->files->makeDirectory(__DIR__.'/../../../resources/dist-dev', 0755, true, true);
    }
}
