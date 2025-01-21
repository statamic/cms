<?php

namespace Tests\Filesystem;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Filesystem\FilesystemAdapter;
use Tests\TestCase;

class FilesystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    private $tempDir;
    private $baseDir;
    private $outsideRoot;
    private $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);
        $this->baseDir = $this->tempDir;
        mkdir($this->outsideRoot = $this->tempDir.'/outside-root');
        mkdir($this->tempDir = $this->tempDir.'/root');

        $this->adapter = $this->makeAdapter();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory($this->baseDir);
    }

    protected function makeAdapter()
    {
        return new FilesystemAdapter(
            $this->filesystem = new Filesystem,
            $this->tempDir
        );
    }

    #[Test]
    public function it_normalizes_relative_paths()
    {
        $dir = Path::tidy($this->tempDir);
        $this->assertEquals($dir.'/bar.txt', $this->adapter->normalizePath('bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath('foo/bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath('foo\bar.txt'));
    }

    #[Test]
    public function it_normalizes_absolute_paths()
    {
        $dir = Path::tidy($this->tempDir);
        $this->assertEquals($dir.'/bar.txt', $this->adapter->normalizePath($this->tempDir.'/bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'/foo/bar.txt'));
        $this->assertEquals($dir.'/bar.txt', $this->adapter->normalizePath($this->tempDir.'\bar.txt'));
        $this->assertEquals($dir.'/foo/bar.txt', $this->adapter->normalizePath($this->tempDir.'\foo\bar.txt'));
    }

    #[Test]
    public function it_normalizes_absolute_paths_outside_the_root()
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

    #[Test]
    public function it_checks_if_a_path_is_within_the_root()
    {
        $this->assertTrue($this->adapter->isWithinRoot('relative/test.txt'));
        $this->assertTrue($this->adapter->isWithinRoot($this->tempDir.'/test.txt'));
        $this->assertFalse($this->adapter->isWithinRoot('/absolute/path/test.txt'));
        $this->assertFalse($this->adapter->isWithinRoot('C:\windows\path\test.txt'));
        $this->assertFalse($this->adapter->isWithinRoot('C:/windows/path/test.txt'));

        // test with the root with backslashes just to be sure.
        $this->assertTrue($this->adapter->isWithinRoot(
            str_replace('/', '\\', $this->tempDir).'/test.txt')
        );
    }

    #[Test]
    public function it_gets_files_from_outside_of_the_root_and_outputs_absolute_paths()
    {
        mkdir($this->outsideRoot.'/sub', 0755, true);
        file_put_contents($this->outsideRoot.'/sub/one.txt', '');
        file_put_contents($this->outsideRoot.'/sub/two.txt', '');

        $dir = Path::tidy($this->outsideRoot);

        $this->assertArraysHaveSameValues([
            $dir.'/sub/one.txt',
            $dir.'/sub/two.txt',
        ], $this->adapter->getFiles($dir.'/sub')->all());
    }

    #[Test]
    public function it_can_explicitly_request_absolute_paths()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');

        $return = $this->adapter->withAbsolutePaths();
        $this->assertEquals($this->adapter, $return);

        $files = $this->adapter->getFiles('sub');
        $dir = Path::tidy($this->tempDir);
        $this->assertArraysHaveSameValues(
            [
                $dir.'/sub/two.txt',
                $dir.'/sub/three.txt',
            ],
            $files->all()
        );
    }
}
