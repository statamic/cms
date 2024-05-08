<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeAddonTest extends TestCase
{
    use Concerns\CleansUpGeneratedPaths,
        Concerns\FakesComposerInstalls;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->markTestSkippedInWindows();

        $this->files = app(Filesystem::class);
        $this->fakeSuccessfulComposerRequire();
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    /** @test */
    public function it_can_generate_an_addon()
    {
        $this->assertFileDoesNotExist(base_path('addons/hasselhoff/knight-rider'));

        $this->makeAddon('hasselhoff/knight-rider');

        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/README.md'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/.gitignore'));

        $this->assertFileExists($composerJson = base_path('addons/hasselhoff/knight-rider/composer.json'));
        $this->assertStringContainsString('"Hasselhoff\\\KnightRider\\\": "src"', $this->files->get($composerJson));

        $this->assertFileExists($provider = base_path('addons/hasselhoff/knight-rider/src/ServiceProvider.php'));
        $this->assertStringContainsString('namespace Hasselhoff\KnightRider;', $this->files->get($provider));
    }

    /** @test */
    public function it_cannot_make_addon_with_invalid_composer_package_name()
    {
        $this->artisan('statamic:make:addon', ['addon' => 'deaths-tar-vulnerability'])
            ->expectsOutput('Please enter a valid composer package name (eg. hasselhoff/kung-fury).');

        $this->artisan('statamic:make:addon', ['addon' => 'some/path/deaths-tar-vulnerability'])
            ->expectsOutput('Please enter a valid composer package name (eg. hasselhoff/kung-fury).');

        $this->assertFileDoesNotExist(base_path('addons/erso/deaths-tar-vulnerability'));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_addon()
    {
        $path = base_path('addons/erso/deaths-tar-vulnerability');

        $this->artisan('statamic:make:addon', ['addon' => 'erso/deaths-tar-vulnerability']);
        $this->files->put("$path/src/ServiceProvider.php", 'overwritten stuff');

        $this->assertStringContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));

        $this->artisan('statamic:make:addon', ['addon' => 'erso/deaths-tar-vulnerability'])
            ->expectsOutput('Addon already exists!');

        $this->assertStringContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_addon()
    {
        $path = base_path('addons/erso/deaths-tar-vulnerability');

        // Setup addon with custom service provider
        $this->artisan('statamic:make:addon', ['addon' => 'erso/deaths-tar-vulnerability']);
        $this->files->put("$path/src/ServiceProvider.php", 'overwritten stuff');
        $this->assertStringContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));

        // Use force option to ensure service provider stub gets copied
        $this->artisan('statamic:make:addon', ['addon' => 'erso/deaths-tar-vulnerability', '--force' => true]);
        $this->assertStringNotContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    public function it_can_generate_with_a_fieldtype()
    {
        $this->assertFileDoesNotExist(base_path('addons/hasselhoff/knight-rider'));

        $this->makeAddon('hasselhoff/knight-rider', ['--fieldtype' => true]);

        // Standard addon stuff
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/README.md'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/.gitignore'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/composer.json'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/src/ServiceProvider.php'));

        // Fieldtype stuff
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/package.json'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/vite.config.js'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/src/Fieldtypes/KnightRider.php'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/resources/js/addon.js'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/resources/js/components/fieldtypes/KnightRider.vue'));
        $this->assertDirectoryExists(base_path('addons/hasselhoff/knight-rider/resources/dist'));
    }

    /** @test */
    public function it_can_make_an_addon_with_everything_including_the_kitchen_sink()
    {
        $path = base_path('addons/ford/san-holo');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:addon', ['addon' => 'ford/san-holo', '--all' => true]);

        $this->assertFileExists("$path/src/Fieldtypes/SanHolo.php");
        $this->assertStringContainsString('namespace Ford\SanHolo\Fieldtypes;', $this->files->get("$path/src/Fieldtypes/SanHolo.php"));
        $this->assertFileExists("$path/src/Scopes/SanHolo.php");
        $this->assertStringContainsString('namespace Ford\SanHolo\Scopes;', $this->files->get("$path/src/Scopes/SanHolo.php"));
        $this->assertFileExists("$path/src/Modifiers/SanHolo.php");
        $this->assertStringContainsString('namespace Ford\SanHolo\Modifiers;', $this->files->get("$path/src/Modifiers/SanHolo.php"));
        $this->assertFileExists("$path/src/Tags/SanHolo.php");

        $this->assertStringContainsString('namespace Ford\SanHolo\Tags;', $this->files->get("$path/src/Tags/SanHolo.php"));
        $this->assertFileExists("$path/src/Widgets/SanHolo.php");
        $this->assertStringContainsString('namespace Ford\SanHolo\Widgets;', $this->files->get("$path/src/Widgets/SanHolo.php"));
    }

    private function makeAddon($addon, $options = [])
    {
        $this->artisan('statamic:make:addon', array_merge([
            'addon' => $addon,
            '--no-interaction' => true,
        ], $options));
    }
}
