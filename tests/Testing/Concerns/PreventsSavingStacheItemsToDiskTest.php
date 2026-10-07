<?php

namespace Tests\Testing\Concerns;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class PreventsSavingStacheItemsToDiskTest extends TestCase
{
    private string $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/statamic-fake-stache-'.uniqid();
        mkdir($this->directory);
        file_put_contents($this->directory.'/entry.yaml', 'title: Leftover');
    }

    public function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function it_empties_and_recreates_the_fake_stache_directory()
    {
        $this->makeTestCase()->deleteFakeStacheDirectory();

        $this->assertDirectoryExists($this->directory);
        $this->assertFileDoesNotExist($this->directory.'/entry.yaml');
        $this->assertFileExists($this->directory.'/.gitkeep');
    }

    #[Test]
    public function it_does_not_fail_when_the_directory_outlives_its_deletion()
    {
        // Empties the directory but leaves it in place, like a delete on Windows while a handle is still open.
        $this->app->instance('files', new class extends Filesystem
        {
            public function deleteDirectory($directory, $preserve = false)
            {
                return parent::deleteDirectory($directory, true);
            }
        });

        $this->makeTestCase()->deleteFakeStacheDirectory();

        $this->assertDirectoryExists($this->directory);
        $this->assertFileDoesNotExist($this->directory.'/entry.yaml');
        $this->assertFileExists($this->directory.'/.gitkeep');
    }

    private function makeTestCase()
    {
        return new class($this->directory)
        {
            use PreventsSavingStacheItemsToDisk {
                deleteFakeStacheDirectory as public;
            }

            public function __construct(public $fakeStacheDirectory)
            {
            }
        };
    }
}
