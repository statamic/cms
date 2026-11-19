<?php

namespace Tests\Imaging;

use Facades\Statamic\Imaging\Attributes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter as FlysystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Tests\TestCase;

class AttributesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        File::delete($this->cachePath());

        Storage::extend('non-local', function ($app, $config) {
            $adapter = new NonLocalAdapter(new LocalFilesystemAdapter($config['root']));

            return new FilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }

    public function tearDown(): void
    {
        File::delete($this->cachePath());

        parent::tearDown();
    }

    #[Test]
    public function it_gets_attributes_from_a_local_disk()
    {
        $disk = $this->disk('local');
        $disk->putFileAs('foo', UploadedFile::fake()->image('bar.jpg', 30, 60), 'bar.jpg');

        $this->assertEquals(['width' => 30, 'height' => 60], Attributes::from($disk, 'foo/bar.jpg'));
        $this->assertEmpty(File::getFilesRecursively($this->cachePath()));
    }

    #[Test]
    public function it_gets_attributes_from_a_non_local_disk()
    {
        $disk = $this->disk('non-local');
        $disk->putFileAs('foo', UploadedFile::fake()->image('bar.jpg', 30, 60), 'bar.jpg');

        $this->assertEquals(['width' => 30, 'height' => 60], Attributes::from($disk, 'foo/bar.jpg'));
        $this->assertEmpty(File::getFilesRecursively($this->cachePath()));
    }

    #[Test]
    public function it_gets_attributes_of_an_svg_on_a_non_local_disk()
    {
        $disk = $this->disk('non-local');
        $disk->put('foo/bar.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="90"></svg>');

        $this->assertEquals(['width' => 45.0, 'height' => 90.0], Attributes::from($disk, 'foo/bar.svg'));
    }

    #[Test]
    public function it_does_not_touch_temporary_files_belonging_to_other_requests()
    {
        $disk = $this->disk('non-local');
        $disk->putFileAs('foo', UploadedFile::fake()->image('bar.jpg', 30, 60), 'bar.jpg');

        $cache = Storage::build(['driver' => 'local', 'root' => $this->cachePath()]);
        $cache->put('foo/bar.jpg', 'another request is using this');

        $this->assertEquals(['width' => 30, 'height' => 60], Attributes::from($disk, 'foo/bar.jpg'));
        $this->assertSame('another request is using this', $cache->get('foo/bar.jpg'));
    }

    #[Test]
    public function it_does_not_use_a_local_source_disk_as_the_temporary_cache_disk()
    {
        $local = $this->disk('local');
        $local->putFileAs('foo', UploadedFile::fake()->image('bar.jpg', 30, 60), 'bar.jpg');
        $local->put('baz/qux.jpg', 'this file has nothing to do with the non-local disk');

        Attributes::from($local, 'foo/bar.jpg');

        $nonLocal = $this->disk('non-local');
        $nonLocal->putFileAs('baz', UploadedFile::fake()->image('qux.jpg', 120, 240), 'qux.jpg');

        $this->assertEquals(['width' => 120, 'height' => 240], Attributes::from($nonLocal, 'baz/qux.jpg'));
        $this->assertSame('this file has nothing to do with the non-local disk', $local->get('baz/qux.jpg'));
    }

    private function disk(string $driver): FilesystemAdapter
    {
        $root = storage_path('statamic/test-'.$driver);

        File::delete($root);

        return Storage::build(['driver' => $driver, 'root' => $root]);
    }

    private function cachePath(): string
    {
        return storage_path('statamic/attributes-cache');
    }
}

class NonLocalAdapter implements FlysystemAdapter
{
    public function __construct(private FlysystemAdapter $adapter)
    {
    }

    public function fileExists(string $path): bool
    {
        return $this->adapter->fileExists($path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->adapter->directoryExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->adapter->write($path, $contents, $config);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->adapter->writeStream($path, $contents, $config);
    }

    public function read(string $path): string
    {
        return $this->adapter->read($path);
    }

    public function readStream(string $path)
    {
        return $this->adapter->readStream($path);
    }

    public function delete(string $path): void
    {
        $this->adapter->delete($path);
    }

    public function deleteDirectory(string $path): void
    {
        $this->adapter->deleteDirectory($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($path, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);
    }

    public function visibility(string $path): \League\Flysystem\FileAttributes
    {
        return $this->adapter->visibility($path);
    }

    public function mimeType(string $path): \League\Flysystem\FileAttributes
    {
        return $this->adapter->mimeType($path);
    }

    public function lastModified(string $path): \League\Flysystem\FileAttributes
    {
        return $this->adapter->lastModified($path);
    }

    public function fileSize(string $path): \League\Flysystem\FileAttributes
    {
        return $this->adapter->fileSize($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return $this->adapter->listContents($path, $deep);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($source, $destination, $config);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($source, $destination, $config);
    }
}
