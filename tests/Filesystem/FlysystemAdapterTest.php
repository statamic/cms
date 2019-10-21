<?php

namespace Tests\Filesystem;

use Tests\TestCase;
use League\Flysystem\Adapter\Local;
use Statamic\Filesystem\FlysystemAdapter;
use Statamic\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem as Flysystem;
use Illuminate\Filesystem\FilesystemAdapter as IlluminateFilesystemAdapter;

class FlysystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    protected function makeAdapter()
    {
        // Equivalent to `Storage::disk()`
        $this->filesystem = new IlluminateFilesystemAdapter(
            new Flysystem(new Local($this->tempDir))
        );

        return new FlysystemAdapter($this->filesystem);
    }

    /** @test */
    function it_normalizes_relative_paths()
    {
        $this->assertEquals('bar.txt', $this->adapter->normalizePath('bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath('foo/bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath('foo\bar.txt'));
        $this->assertEquals('/', $this->adapter->normalizePath('/'));
        $this->assertEquals('/', $this->adapter->normalizePath('.'));
    }

    /** @test */
    function it_normalizes_absolute_paths()
    {
        $this->assertEquals('bar.txt', $this->adapter->normalizePath($this->tempDir.'/bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'/foo/bar.txt'));
        $this->assertEquals('bar.txt', $this->adapter->normalizePath($this->tempDir.'\bar.txt'));
        $this->assertEquals('foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'\foo\bar.txt'));
    }

    /** @test */
    function it_throws_an_exception_when_normalizing_a_path_outside_the_root()
    {
        $this->expectException(\LogicException::class);

        $this->adapter->normalizePath('/not/the/temp/dir/bar.txt');
    }

    /** @test */
    function it_throws_exception_when_normalizing_an_absolute_path_on_a_non_local_adapter()
    {
        $this->markTestIncomplete();
    }
}
