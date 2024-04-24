<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeModifierTest extends TestCase
{
    use Concerns\CleansUpGeneratedPaths,
        Concerns\FakesComposerInstalls;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->fakeSuccessfulComposerRequire();
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    /** @test */
    public function it_can_make_a_modifier()
    {
        $path = base_path('app/Modifiers/Giraffe.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Modifiers;', $this->files->get($path));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_modifier()
    {
        $path = base_path('app/Modifiers/Giraffe.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe']);
        $this->files->put($path, 'overwritten modifier');

        $this->assertStringContainsString('overwritten modifier', $this->files->get($path));

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe']);

        $this->assertStringContainsString('overwritten modifier', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_modifier()
    {
        $path = base_path('app/Modifiers/Giraffe.php');

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe']);
        $this->files->put($path, 'overwritten modifier');

        $this->assertStringContainsString('overwritten modifier', $this->files->get($path));

        $this->artisan('statamic:make:modifier', ['name' => 'Giraffe', '--force' => true]);

        $this->assertStringNotContainsString('overwritten modifier', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_modifier_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($modifier = "$path/src/Modifiers/Yoda.php");

        $this->artisan('statamic:make:modifier', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($modifier);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Modifiers;', $this->files->get($modifier));
    }
}
