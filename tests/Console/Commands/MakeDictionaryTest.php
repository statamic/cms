<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MakeDictionaryTest extends TestCase
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

    #[Test]
    public function it_can_make_a_dictionary()
    {
        $path = base_path('app/Dictionaries/Provinces.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:dictionary', ['name' => 'Provinces']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Dictionaries;', $this->files->get($path));
    }

    #[Test]
    public function it_will_not_overwrite_an_existing_dictionary()
    {
        $path = base_path('app/Dictionaries/Provinces.php');

        $this->artisan('statamic:make:dictionary', ['name' => 'Provinces']);
        $this->files->put($path, 'overwritten action');

        $this->assertStringContainsString('overwritten action', $this->files->get($path));

        $this->artisan('statamic:make:dictionary', ['name' => 'Provinces']);

        $this->assertStringContainsString('overwritten action', $this->files->get($path));
    }

    #[Test]
    public function using_force_option_will_overwrite_original_dictionary()
    {
        $path = base_path('app/Dictionaries/Provinces.php');

        $this->artisan('statamic:make:dictionary', ['name' => 'Provinces']);
        $this->files->put($path, 'overwritten action');

        $this->assertStringContainsString('overwritten action', $this->files->get($path));

        $this->artisan('statamic:make:dictionary', ['name' => 'Provinces', '--force' => true]);

        $this->assertStringNotContainsString('overwritten action', $this->files->get($path));
    }

    #[Test]
    public function it_can_make_a_dictionary_into_an_addon()
    {
        $path = base_path('addons/yoda/bag-odah');

        $this->artisan('statamic:make:addon', ['addon' => 'yoda/bag-odah']);

        Composer::shouldReceive('installedPath')->andReturn($path);

        $this->assertFileDoesNotExist($action = "$path/src/Dictionaries/Provinces.php");

        $this->artisan('statamic:make:dictionary', ['name' => 'Provinces', 'addon' => 'yoda/bag-odah']);

        $this->assertFileExists($action);
        $this->assertStringContainsString('namespace Yoda\BagOdah\Dictionaries;', $this->files->get($action));
    }
}
