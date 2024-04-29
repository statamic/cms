<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeTagTest extends TestCase
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
    public function it_can_make_a_tag()
    {
        $path = base_path('app/Tags/Donkey.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:tag', ['name' => 'Donkey']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Tags;', $this->files->get($path));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_tag()
    {
        $path = base_path('app/Tags/Donkey.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:tag', ['name' => 'Donkey']);
        $this->files->put($path, 'overwritten tag');

        $this->assertStringContainsString('overwritten tag', $this->files->get($path));

        $this->artisan('statamic:make:tag', ['name' => 'Donkey']);

        $this->assertStringContainsString('overwritten tag', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_tag()
    {
        $path = base_path('app/Tags/Donkey.php');

        $this->artisan('statamic:make:tag', ['name' => 'Donkey']);
        $this->files->put($path, 'overwritten tag');

        $this->assertStringContainsString('overwritten tag', $this->files->get($path));

        $this->artisan('statamic:make:tag', ['name' => 'Donkey', '--force' => true]);

        $this->assertStringNotContainsString('overwritten tag', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_tag_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($tag = "$path/src/Tags/Yoda.php");

        $this->artisan('statamic:make:tag', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($tag);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Tags;', $this->files->get($tag));
    }
}
