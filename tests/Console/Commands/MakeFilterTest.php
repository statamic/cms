<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeFilterTest extends TestCase
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
    public function it_can_make_a_filter()
    {
        $path = base_path('app/Scopes/Mouse.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:filter', ['name' => 'Mouse']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Scopes;', $this->files->get($path));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_filter()
    {
        $path = base_path('app/Scopes/Mouse.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:filter', ['name' => 'Mouse']);
        $this->files->put($path, 'overwritten filter');

        $this->assertStringContainsString('overwritten filter', $this->files->get($path));

        $this->artisan('statamic:make:filter', ['name' => 'Mouse']);

        $this->assertStringContainsString('overwritten filter', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_filter()
    {
        $path = base_path('app/Scopes/Mouse.php');

        $this->artisan('statamic:make:filter', ['name' => 'Mouse']);
        $this->files->put($path, 'overwritten filter');

        $this->assertStringContainsString('overwritten filter', $this->files->get($path));

        $this->artisan('statamic:make:filter', ['name' => 'Mouse', '--force' => true]);

        $this->assertStringNotContainsString('overwritten filter', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_filter_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($filter = "$path/src/Scopes/Yoda.php");

        $this->artisan('statamic:make:filter', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($filter);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Scopes;', $this->files->get($filter));
    }
}
