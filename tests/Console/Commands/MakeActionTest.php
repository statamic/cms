<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeActionTest extends TestCase
{
    use Concerns\CleansUpGeneratedPaths;

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
    public function it_can_make_an_action()
    {
        $path = base_path('app/Actions/Delete.php');

        $this->assertFileNotExists($path);

        $this->artisan('statamic:make:action', ['name' => 'Delete']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Actions;', $this->files->get($path));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_action()
    {
        $path = base_path('app/Actions/Delete.php');

        $this->artisan('statamic:make:action', ['name' => 'Delete']);
        $this->files->put($path, 'overwritten action');

        $this->assertStringContainsString('overwritten action', $this->files->get($path));

        $this->artisan('statamic:make:action', ['name' => 'Delete']);

        $this->assertStringContainsString('overwritten action', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_action()
    {
        $path = base_path('app/Actions/Delete.php');

        $this->artisan('statamic:make:action', ['name' => 'Delete']);
        $this->files->put($path, 'overwritten action');

        $this->assertStringContainsString('overwritten action', $this->files->get($path));

        $this->artisan('statamic:make:action', ['name' => 'Delete', '--force' => true]);

        $this->assertStringNotContainsString('overwritten action', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_an_action_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileNotExists($action = "$path/src/Actions/Yoda.php");

        $this->artisan('statamic:make:action', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($action);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Actions;', $this->files->get($action));
    }
}
