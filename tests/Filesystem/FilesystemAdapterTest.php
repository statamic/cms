<?php

namespace Tests\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Path;
use Statamic\Filesystem\FilesystemAdapter;
use Tests\TestCase;

class FilesystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    protected function makeAdapter()
    {
        return new FilesystemAdapter(
            $this->filesystem = new Filesystem,
            $this->tempDir
        );
    }

    /** @test */
    function it_normalizes_relative_paths()
    {
        $dir = Path::tidy($this->tempDir);
        $this->assertEquals($dir.'/bar.txt', $this->adapter->normalizePath('bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath('foo/bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath('foo\bar.txt'));
    }

    /** @test */
    function it_normalizes_absolute_paths()
    {
        $dir = Path::tidy($this->tempDir);
        $this->assertEquals($dir.'/bar.txt', $this->adapter->normalizePath($this->tempDir.'/bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'/foo/bar.txt'));
        $this->assertEquals($dir.'/bar.txt', $this->adapter->normalizePath($this->tempDir.'\bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'\foo\bar.txt'));
    }

    /** @test */
    function it_normalizes_absolute_paths_outside_the_root()
    {
        // unix
        $this->assertEquals('/path/to/bar.txt', $this->adapter->normalizePath('/path/to/bar.txt'));
        $this->assertEquals('/path/to/foo/bar.txt', $this->adapter->normalizePath('/path/to/foo/bar.txt'));
        $this->assertEquals('/path/to/bar.txt', $this->adapter->normalizePath('/path/to\bar.txt'));
        $this->assertEquals('/path/to/foo/bar.txt', $this->adapter->normalizePath('/path/to\foo\bar.txt'));

        // windows with forward slashes
        $this->assertEquals('C:/path/to/bar.txt', $this->adapter->normalizePath('C:/path/to/bar.txt'));
        $this->assertEquals('C:/path/to/foo/bar.txt', $this->adapter->normalizePath('C:/path/to/foo/bar.txt'));
        $this->assertEquals('C:/path/to/bar.txt', $this->adapter->normalizePath('C:/path/to\bar.txt'));
        $this->assertEquals('C:/path/to/foo/bar.txt', $this->adapter->normalizePath('C:/path/to\foo\bar.txt'));

        // windows with backslashes
        $this->assertEquals('C:/path/to/bar.txt', $this->adapter->normalizePath('C:\path\to\bar.txt'));
        $this->assertEquals('C:/path/to/foo/bar.txt', $this->adapter->normalizePath('C:\path\to\foo\bar.txt'));
        $this->assertEquals('C:/path/to/bar.txt', $this->adapter->normalizePath('C:\path\to/bar.txt'));
        $this->assertEquals('C:/path/to/foo/bar.txt', $this->adapter->normalizePath('C:\path\to/foo/bar.txt'));
    }
}
