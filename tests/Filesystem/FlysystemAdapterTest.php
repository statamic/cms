<?php

namespace Tests\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter as IlluminateFilesystemAdapter;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as Flysystem;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\TestCase;

class FlysystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->adapter = $this->makeAdapter();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    protected function makeAdapter()
    {
        // Equivalent to `Storage::disk()`
        $this->filesystem = new IlluminateFilesystemAdapter(
            new Flysystem(new Local($this->tempDir))
        );

        return new FlysystemAdapter($this->filesystem);
    }

    /** @test */
    public function gets_fallback_if_a_file_doesnt_exist_and_asserts_are_disabled()
    {
        $this->filesystem->getConfig()->set('disable_asserts', true);

        $this->assertEquals('Hello World', $this->adapter->get('filename.txt', 'Hello World'));
    }

    /** @test */
    public function it_normalizes_relative_paths()
    {
        $this->assertEquals('bar.txt', $this->adapter->normalizePath('bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath('foo/bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath('foo\bar.txt'));
        $this->assertEquals('/', $this->adapter->normalizePath('/'));
        $this->assertEquals('/', $this->adapter->normalizePath('.'));
    }

    /** @test */
    public function it_normalizes_absolute_paths()
    {
        $this->assertEquals('bar.txt', $this->adapter->normalizePath($this->tempDir.'/bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'/foo/bar.txt'));
        $this->assertEquals('bar.txt', $this->adapter->normalizePath($this->tempDir.'\bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'\foo\bar.txt'));
    }

    /** @test */
    public function it_throws_an_exception_when_normalizing_a_path_outside_the_root()
    {
        $this->expectException(\LogicException::class);

        $this->adapter->normalizePath('/not/the/temp/dir/bar.txt');
    }

    /** @test */
    public function it_throws_exception_when_normalizing_an_absolute_path_on_a_non_local_adapter()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_throws_an_exception_when_requesting_absolute_paths()
    {
        $this->expectException(\LogicException::class);

        $this->adapter->withAbsolutePaths();
    }
}
