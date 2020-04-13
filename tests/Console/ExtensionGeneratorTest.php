<?php

namespace Tests\Console;

use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Console\Processes\Composer;
use Tests\Console\Foundation\InteractsWithConsole;

class ExtensionGeneratorTest extends TestCase
{
    public $testedPaths = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    /** @test */
    function it_can_make_a_fieldtype()
    {
        $path = $this->preparePath('app/Fieldtypes/Cat.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:fieldtype', ['name' => 'Cat']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Fieldtypes;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_scope()
    {
        $path = $this->preparePath('app/Scopes/Dog.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:scope', ['name' => 'Dog']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Scopes;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_modifier()
    {
        $path = $this->preparePath('app/Modifiers/Giraffe.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Modifiers;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_tag()
    {
        $path = $this->preparePath('app/Tags/Donkey.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:tag', ['name' => 'Donkey']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Tags;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_widget()
    {
        $path = $this->preparePath('app/Widgets/Sloth.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Widgets;', $this->files->get($path));
    }

    /** @test */
    function it_will_not_overwrite_an_existing_extension()
    {
        $path = $this->preparePath('app/Widgets/Sloth.php');

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);
        $this->files->put($path, 'overwritten stuff');

        $this->assertStringContainsString('overwritten stuff', $this->files->get($path));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertStringContainsString('overwritten stuff', $this->files->get($path));
    }

    /** @test */
    function using_force_option_will_overwrite_original_extension()
    {
        $path = $this->preparePath('app/Widgets/Sloth.php');

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);
        $this->files->put($path, 'overwritten stuff');

        $this->assertStringContainsString('overwritten stuff', $this->files->get($path));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth', '--force' => null]);

        $this->assertStringNotContainsString('overwritten stuff', $this->files->get($path));
    }

    /** @test */
    function it_can_make_an_addon()
    {
        $path = $this->preparePath('addons/erso/deaths-tar-vulnerability');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:addon', ['package' => 'erso/deaths-tar-vulnerability']);

        $this->assertFileExists($composerJson = "$path/composer.json");
        $this->assertStringContainsString('"Erso\\\DeathsTarVulnerability\\\": "src"', $this->files->get($composerJson));
        $this->assertFileExists($provider = "$path/src/ServiceProvider.php");
        $this->assertStringContainsString('namespace Erso\DeathsTarVulnerability;', $this->files->get($provider));
    }

    /** @test */
    function it_cannot_make_addon_with_invalid_composer_package_name()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $path = $this->preparePath('addons/erso/deaths-tar-vulnerability');

        $this->artisan('statamic:make:addon', ['package' => 'deaths-tar-vulnerability'])
            ->expectsOutput('Please enter a valid composer package name (eg. john/my-addon).');

        $this->artisan('statamic:make:addon', ['package' => 'some/path/deaths-tar-vulnerability'])
            ->expectsOutput('Please enter a valid composer package name (eg. john/my-addon).');

        $this->assertFileNotExists($path);
    }

    /** @test */
    function it_will_not_overwrite_an_existing_addon()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $path = $this->preparePath('addons/erso/deaths-tar-vulnerability');

        $this->artisan('statamic:make:addon', ['package' => 'erso/deaths-tar-vulnerability']);
        $this->files->put("$path/src/ServiceProvider.php", 'overwritten stuff');

        $this->assertStringContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));

        $this->artisan('statamic:make:addon', ['package' => 'erso/deaths-tar-vulnerability'])
            ->expectsOutput('Addon already exists!');

        $this->assertStringContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    function using_force_option_will_overwrite_original_addon()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $path = $this->preparePath('addons/erso/deaths-tar-vulnerability');

        $this->artisan('statamic:make:addon', ['package' => 'erso/deaths-tar-vulnerability']);
        $this->files->put("$path/src/ServiceProvider.php", 'overwritten stuff');

        $this->assertStringContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));

        $this->artisan('statamic:make:addon', ['package' => 'erso/deaths-tar-vulnerability', '--force' => null]);

        $this->assertStringNotContainsString('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    function it_can_make_an_addon_with_an_extension()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $path = $this->preparePath('addons/ford/san-holo');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:addon', ['package' => 'ford/san-holo', '--tag' => null]);

        $this->assertFileExists("$path/src/Tags/SanHolo.php");
        $this->assertStringContainsString('namespace Ford\SanHolo\Tags;', $this->files->get("$path/src/Tags/SanHolo.php"));
    }

    /** @test */
    function it_can_make_an_addon_with_everything_including_the_kitchen_sink()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $path = $this->preparePath('addons/ford/san-holo');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:addon', ['package' => 'ford/san-holo', '--all' => null]);

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

    /** @test */
    function it_can_make_an_extension_into_an_addon()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        $path = $this->preparePath('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['package' => 'yoda/bag-odah']);

        $this->assertFileNotExists("$path/src/Tags/Yoda.php");

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->artisan('statamic:make:tag', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists("$path/src/Tags/Yoda.php");
        $this->assertStringContainsString('namespace Yoda\BagOdah\Tags;', $this->files->get("$path/src/Tags/Yoda.php"));
    }

    private function preparePath($path)
    {
        $path = base_path($path);

        $this->testedPaths[] = $path;

        return $path;
    }

    private function cleanupPaths()
    {
        foreach ($this->testedPaths as $path) {
            $this->files->isDirectory($path)
                ? $this->files->deleteDirectory($path)
                : $this->files->delete($path);
        }

        $dirs = [
            base_path('addons'),
            base_path('app/Fieldtypes'),
            base_path('app/Scopes'),
            base_path('app/Tags'),
            base_path('app/Widgets'),
        ];

        foreach ($dirs as $dir) {
            $this->files->deleteDirectory($dir, true);
        }
    }
}
