<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeFieldtypeTest extends TestCase
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
    public function it_can_generate_a_fieldtype()
    {
        $this->assertFileDoesNotExist(base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertFileDoesNotExist(resource_path('js/components/fieldtypes/KnightRider.vue'));

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider']);

        $this->assertFileExists($fieldtype = base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertStringContainsString('namespace App\Fieldtypes;', $this->files->get($fieldtype));

        $this->assertFileExists(resource_path('js/components/fieldtypes/KnightRider.vue'));

        // @TODO: Test for webpack/cp.js injection or output instructions
    }

    /** @test */
    public function it_will_not_overwrite_an_existing_fieldtype()
    {
        $path = base_path('app/Fieldtypes/KnightRider.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider']);
        $this->files->put($path, 'overwritten fieldtype');

        $this->assertStringContainsString('overwritten fieldtype', $this->files->get($path));

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider']);

        $this->assertStringContainsString('overwritten fieldtype', $this->files->get($path));
    }

    /** @test */
    public function using_force_option_will_overwrite_original_fieldtype()
    {
        $path = base_path('app/Fieldtypes/KnightRider.php');

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider']);
        $this->files->put($path, 'overwritten fieldtype');

        $this->assertStringContainsString('overwritten fieldtype', $this->files->get($path));

        $this->artisan('statamic:make:fieldtype', ['name' => 'KnightRider', '--force' => true]);

        $this->assertStringNotContainsString('overwritten fieldtype', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_fieldtype_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($fieldtype = "$path/src/Fieldtypes/Yoda.php");

        $this->artisan('statamic:make:fieldtype', ['name' => 'Yoda', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($fieldtype);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Fieldtypes;', $this->files->get($fieldtype));
    }
}
