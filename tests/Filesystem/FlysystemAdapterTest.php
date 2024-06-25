<?php

namespace Tests\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\TestCase;

class FlysystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    private $tempDir;
    private $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $this->adapter = $this->makeAdapter();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    protected function makeAdapter()
    {
        $this->filesystem = Storage::build([
            'driver' => 'local',
            'root' => $this->tempDir,
        ]);

        return new FlysystemAdapter($this->filesystem);
    }

    #[Test]
    public function it_normalizes_relative_paths()
    {
        $this->assertEquals('bar.txt', $this->adapter->normalizePath('bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath('foo/bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath('foo\bar.txt'));
        $this->assertEquals('/', $this->adapter->normalizePath('/'));
        $this->assertEquals('/', $this->adapter->normalizePath('.'));
    }

    #[Test]
    public function it_normalizes_absolute_paths()
    {
        $this->assertEquals('bar.txt', $this->adapter->normalizePath($this->tempDir.'/bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'/foo/bar.txt'));
        $this->assertEquals('bar.txt', $this->adapter->normalizePath($this->tempDir.'\bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'\foo\bar.txt'));
    }

    #[Test]
    public function it_throws_an_exception_when_normalizing_a_path_outside_the_root()
    {
        $this->expectException(\LogicException::class);

        $this->adapter->normalizePath('/not/the/temp/dir/bar.txt');
    }

    #[Test]
    public function it_throws_exception_when_normalizing_an_absolute_path_on_a_non_local_adapter()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_throws_an_exception_when_requesting_absolute_paths()
    {
        $this->expectException(\LogicException::class);

        $this->adapter->withAbsolutePaths();
    }
}
