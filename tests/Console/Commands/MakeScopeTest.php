<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeScopeTest extends TestCase
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
    public function it_can_make_a_scope()
    {
        $path = base_path('app/Scopes/Dog.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:scope', ['name' => 'Dog']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Scopes;', $this->files->get($path));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_scope()
    {
        $path = base_path('app/Scopes/Dog.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:scope', ['name' => 'Dog']);
        $this->files->put($path, 'overwritten scope');

        $this->assertStringContainsString('overwritten scope', $this->files->get($path));

        $this->artisan('statamic:make:scope', ['name' => 'Dog']);

        $this->assertStringContainsString('overwritten scope', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_scope()
    {
        $path = base_path('app/Scopes/Dog.php');

        $this->artisan('statamic:make:scope', ['name' => 'Dog']);
        $this->files->put($path, 'overwritten scope');

        $this->assertStringContainsString('overwritten scope', $this->files->get($path));

        $this->artisan('statamic:make:scope', ['name' => 'Dog', '--force' => true]);

        $this->assertStringNotContainsString('overwritten scope', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_scope_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($scope = "$path/src/Scopes/Yoda.php");

        $this->artisan('statamic:make:scope', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($scope);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Scopes;', $this->files->get($scope));
    }
}
