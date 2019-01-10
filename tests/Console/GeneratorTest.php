<?php

namespace Tests\Console;

use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Console\Processes\Composer;
use Tests\Console\Foundation\InteractsWithConsole;

class GeneratorTest extends TestCase
{
    public $testedPaths = [];

    public function setUp()
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
    }

    public function tearDown()
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
        $this->assertContains('namespace App\Fieldtypes;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_filter()
    {
        $path = $this->preparePath('app/Filters/Dog.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:filter', ['name' => 'Dog']);

        $this->assertFileExists($path);
        $this->assertContains('namespace App\Filters;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_modifier()
    {
        $path = $this->preparePath('app/Modifiers/Giraffe.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe']);

        $this->assertFileExists($path);
        $this->assertContains('namespace App\Modifiers;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_tag()
    {
        $path = $this->preparePath('app/Tags/Donkey.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:tag', ['name' => 'Donkey']);

        $this->assertFileExists($path);
        $this->assertContains('namespace App\Tags;', $this->files->get($path));
    }

    /** @test */
    function it_can_make_a_widget()
    {
        $path = $this->preparePath('app/Widgets/Sloth.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertFileExists($path);
        $this->assertContains('namespace App\Widgets;', $this->files->get($path));
    }

    /** @test */
    function it_will_not_overwrite_an_existing_extension()
    {
        $path = $this->preparePath('app/Widgets/Sloth.php');

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);
        $this->files->put($path, 'overwritten stuff');

        $this->assertContains('overwritten stuff', $this->files->get($path));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertContains('overwritten stuff', $this->files->get($path));
    }

    /** @test */
    function using_force_option_will_overwrite_original_extension()
    {
        $path = $this->preparePath('app/Widgets/Sloth.php');

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);
        $this->files->put($path, 'overwritten stuff');

        $this->assertContains('overwritten stuff', $this->files->get($path));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth', '--force' => null]);

        $this->assertNotContains('overwritten stuff', $this->files->get($path));
    }

    /** @test */
    function it_can_make_an_addon()
    {
        $path = $this->preparePath('addons/deaths-tar-vulnerability');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:addon', ['name' => 'DeathsTarVulnerability']);

        $this->assertFileExists("$path/composer.json");
        $this->assertFileExists("$path/src/ServiceProvider.php");
        $this->assertContains('namespace Local\DeathsTarVulnerability;', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    function it_will_not_overwrite_an_existing_addon()
    {
        $path = $this->preparePath('addons/deaths-tar-vulnerability');

        $this->artisan('statamic:make:addon', ['name' => 'DeathsTarVulnerability']);
        $this->files->put("$path/src/ServiceProvider.php", 'overwritten stuff');

        $this->assertContains('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));

        $this->artisan('statamic:make:addon', ['name' => 'DeathsTarVulnerability']);

        $this->assertContains('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    function using_force_option_will_overwrite_original_addon()
    {
        $path = $this->preparePath('addons/deaths-tar-vulnerability');

        $this->artisan('statamic:make:addon', ['name' => 'DeathsTarVulnerability']);
        $this->files->put("$path/src/ServiceProvider.php", 'overwritten stuff');

        $this->assertContains('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));

        $this->artisan('statamic:make:addon', ['name' => 'DeathsTarVulnerability', '--force' => null]);

        $this->assertNotContains('overwritten stuff', $this->files->get("$path/src/ServiceProvider.php"));
    }

    /** @test */
    function it_can_make_an_addon_with_an_extension()
    {
        $path = $this->preparePath('addons/san-holo');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:addon', ['name' => 'SanHolo', '--tag' => null]);

        $this->assertFileExists("$path/src/Tags/SanHolo.php");
        $this->assertContains('namespace Local\SanHolo\Tags;', $this->files->get("$path/src/Tags/SanHolo.php"));
    }

    /** @test */
    function it_can_make_an_addon_with_everything_including_the_kitchen_sink()
    {
        $path = $this->preparePath('addons/san-holo');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:addon', ['name' => 'SanHolo', '--all' => null]);

        $this->assertFileExists("$path/src/Fieldtypes/SanHolo.php");
        $this->assertContains('namespace Local\SanHolo\Fieldtypes;', $this->files->get("$path/src/Fieldtypes/SanHolo.php"));
        $this->assertFileExists("$path/src/Filters/SanHolo.php");
        $this->assertContains('namespace Local\SanHolo\Filters;', $this->files->get("$path/src/Filters/SanHolo.php"));
        $this->assertFileExists("$path/src/Modifiers/SanHolo.php");
        $this->assertContains('namespace Local\SanHolo\Modifiers;', $this->files->get("$path/src/Modifiers/SanHolo.php"));
        $this->assertFileExists("$path/src/Tags/SanHolo.php");
        $this->assertContains('namespace Local\SanHolo\Tags;', $this->files->get("$path/src/Tags/SanHolo.php"));
        $this->assertFileExists("$path/src/Widgets/SanHolo.php");
        $this->assertContains('namespace Local\SanHolo\Widgets;', $this->files->get("$path/src/Widgets/SanHolo.php"));
    }

    /** @test */
    function it_can_make_an_extension_into_an_addon()
    {
        $path = $this->preparePath('addons/bag-odah');

        $this->artisan('statamic:make:addon', ['name' => 'BagOdah']);

        $this->assertFileNotExists("$path/src/Tags/Yoda.php");

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->artisan('statamic:make:tag', ['name' => 'Yoda', 'addon' => 'local/bag-odah']);

        $this->assertFileExists("$path/src/Tags/Yoda.php");
        $this->assertContains('namespace Local\BagOdah\Tags;', $this->files->get("$path/src/Tags/Yoda.php"));
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
    }
}
