<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeWidgetTest extends TestCase
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
    public function it_can_make_a_widget()
    {
        $path = base_path('app/Widgets/Sloth.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Widgets;', $this->files->get($path));
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_widget()
    {
        $path = base_path('app/Widgets/Sloth.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);
        $this->files->put($path, 'overwritten widget');

        $this->assertStringContainsString('overwritten widget', $this->files->get($path));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);

        $this->assertStringContainsString('overwritten widget', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_widget()
    {
        $path = base_path('app/Widgets/Sloth.php');

        $this->artisan('statamic:make:widget', ['name' => 'Sloth']);
        $this->files->put($path, 'overwritten widget');

        $this->assertStringContainsString('overwritten widget', $this->files->get($path));

        $this->artisan('statamic:make:widget', ['name' => 'Sloth', '--force' => true]);

        $this->assertStringNotContainsString('overwritten widget', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_widget_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($widget = "$path/src/Widgets/Yoda.php");

        $this->artisan('statamic:make:widget', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($widget);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Widgets;', $this->files->get($widget));
    }
}
