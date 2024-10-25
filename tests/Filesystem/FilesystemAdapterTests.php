<?php

namespace Tests\Filesystem;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Statamic\Support\FileCollection;

trait FilesystemAdapterTests
{
    private $adapter;

    #[Test]
    public function it_makes_a_file_collection()
    {
        $collection = $this->adapter->collection(['one', 'two']);
        $this->assertInstanceOf(FileCollection::class, $collection);
        $this->assertEquals(2, $collection->count());
    }

    #[Test]
    public function gets_file_contents()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertEquals('Hello World', $this->adapter->get('filename.txt'));
    }

    #[Test]
    public function gets_fallback_if_file_doesnt_exist()
    {
        $this->assertEquals('Hello World', $this->adapter->get('filename.txt', 'Hello World'));
    }

    #[Test]
    public function checks_if_file_exists()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertTrue($this->adapter->exists('filename.txt'));
        $this->assertFalse($this->adapter->exists('another.txt'));
    }

    #[Test]
    public function cannot_check_if_null_exists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path must be a string.');
        $this->adapter->exists(null);
    }

    #[Test]
    public function puts_contents_into_a_file()
    {
        $this->adapter->put('filename.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/filename.txt', 'Hello World');
    }

    #[Test]
    public function puts_content_into_a_file_in_a_subdirectory()
    {
        $this->adapter->put('subdir/filename.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/subdir/filename.txt', 'Hello World');
    }

    #[Test]
    public function deletes_files()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->adapter->delete('filename.txt');
        $this->assertFileDoesNotExist($this->tempDir.'/filename.txt');
    }

    #[Test]
    public function cannot_delete_null()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path must be a string.');
        $this->adapter->delete(null);
    }

    #[Test]
    public function copies_files()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        $this->assertTrue($this->adapter->copy('src.txt', 'dest.txt'));
        $this->assertFileExists($this->tempDir.'/dest.txt');
        $this->assertFileExists($this->tempDir.'/src.txt');
    }

    #[Test]
    public function copies_files_and_overwrites()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        file_put_contents($this->tempDir.'/dest.txt', 'Existing Content');
        $this->assertTrue($this->adapter->copy('src.txt', 'dest.txt', true));
        $this->assertStringEqualsFile($this->tempDir.'/src.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
    }

    #[Test]
    public function moves_files()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        $this->assertTrue($this->adapter->move('src.txt', 'dest.txt'));
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
        $this->assertFileDoesNotExist($this->tempDir.'/src.txt');
    }

    #[Test]
    public function moves_files_and_overwrites()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        file_put_contents($this->tempDir.'/dest.txt', 'Existing Content');
        $this->assertTrue($this->adapter->move('src.txt', 'dest.txt', true));
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
        $this->assertFileDoesNotExist($this->tempDir.'/src.txt');
    }

    #[Test]
    public function renames_a_file()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        $this->assertTrue($this->adapter->rename('src.txt', 'dest.txt'));
        $this->assertFileDoesNotExist($this->tempDir.'/src.txt');
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
    }

    #[Test]
    public function gets_file_extension()
    {
        $this->assertEquals('jpg', $this->adapter->extension('photo.jpg'));
    }

    #[Test]
    public function gets_mime_type()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertEquals('text/plain', $this->adapter->mimeType('filename.txt'));
    }

    #[Test]
    public function gets_last_modified()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        touch($this->tempDir.'/filename.txt', $time = 1512160249);
        $this->assertEquals($time, $this->adapter->lastModified('filename.txt'));
    }

    #[Test]
    public function gets_file_size()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertEquals(11, $this->adapter->size('filename.txt'));
    }

    // #[Test]
    // function gets_file_size_for_humans()
    // {
    //     // todo
    // }

    #[Test]
    public function checks_if_a_file_is_an_image()
    {
        $this->assertTrue($this->adapter->isImage('test.jpg'));
        $this->assertTrue($this->adapter->isImage('test.jpeg'));
        $this->assertTrue($this->adapter->isImage('test.png'));
        $this->assertTrue($this->adapter->isImage('test.gif'));
        $this->assertTrue($this->adapter->isImage('test.JPG'));
        $this->assertTrue($this->adapter->isImage('test.JPEG'));
        $this->assertTrue($this->adapter->isImage('test.PNG'));
        $this->assertTrue($this->adapter->isImage('test.GIF'));
        $this->assertTrue($this->adapter->isImage('test.webp'));
        $this->assertTrue($this->adapter->isImage('test.WEBP'));
        $this->assertTrue($this->adapter->isImage('test.avif'));
        $this->assertTrue($this->adapter->isImage('test.AVIF'));
        $this->assertFalse($this->adapter->isImage('test.txt'));
    }

    #[Test]
    public function makes_a_directory()
    {
        $this->assertTrue($this->adapter->makeDirectory('directory'));
        $this->assertDirectoryExists($this->tempDir.'/directory');
    }

    #[Test]
    public function gets_files_from_a_directory()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');

        $files = $this->adapter->getFiles('sub');
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertArraysHaveSameValues(
            ['sub/two.txt', 'sub/three.txt'],
            $files->all()
        );

        $files = $this->adapter->getFiles('non-existent-directory');
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertEquals([], $files->all());
    }

    #[Test]
    public function gets_files_from_a_directory_recursively()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');

        $expected = ['sub/two.txt', 'sub/three.txt', 'sub/sub/four.txt'];

        $files = $this->adapter->getFiles('sub', true);
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertArraysHaveSameValues($expected, $files->all());

        $files = $this->adapter->getFilesRecursively('sub');
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertArraysHaveSameValues($expected, $files->all());
    }

    #[Test]
    public function gets_files_recursively_with_directory_exceptions()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        mkdir($this->tempDir.'/sub/exclude', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');
        file_put_contents($this->tempDir.'/sub/exclude/five.txt', '');

        $files = $this->adapter->getFilesRecursivelyExcept('sub', ['exclude']);
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertArraysHaveSameValues(
            ['sub/two.txt', 'sub/three.txt', 'sub/sub/four.txt'],
            $files->all()
        );
    }

    #[Test]
    public function gets_folders()
    {
        mkdir($this->tempDir.'/foo');
        mkdir($this->tempDir.'/foo/bar');
        mkdir($this->tempDir.'/foo/baz');
        mkdir($this->tempDir.'/foo/bar/qux');
        mkdir($this->tempDir.'/baz');
        mkdir($this->tempDir.'/baz/foo');

        $folders = $this->adapter->getFolders('foo');
        $this->assertInstanceOf(Collection::class, $folders);
        $this->assertArraysHaveSameValues(
            ['foo/bar', 'foo/baz'],
            $folders->all()
        );
    }

    #[Test]
    public function gets_folders_recursively()
    {
        mkdir($this->tempDir.'/foo');
        mkdir($this->tempDir.'/foo/bar');
        mkdir($this->tempDir.'/foo/baz');
        mkdir($this->tempDir.'/foo/bar/qux');
        mkdir($this->tempDir.'/baz');
        mkdir($this->tempDir.'/baz/foo');

        $folders = $this->adapter->getFoldersRecursively('foo');
        $this->assertInstanceOf(Collection::class, $folders);
        $this->assertArraysHaveSameValues(
            ['foo/bar', 'foo/baz', 'foo/bar/qux'],
            $folders->all()
        );
    }

    #[Test]
    public function gets_files_by_type()
    {
        mkdir($this->tempDir.'/docs');
        file_put_contents($this->tempDir.'/image.jpg', '');
        file_put_contents($this->tempDir.'/image2.jpg', '');
        file_put_contents($this->tempDir.'/text.txt', '');
        file_put_contents($this->tempDir.'/docs/word.doc', '');
        file_put_contents($this->tempDir.'/docs/test.pdf', '');
        file_put_contents($this->tempDir.'/docs/photo.jpg', '');

        $files = $this->adapter->getFilesByType('/', 'jpg');
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertArraysHaveSameValues(
            ['image.jpg', 'image2.jpg'],
            $files->all()
        );

        $this->assertArraysHaveSameValues(
            ['docs/test.pdf'],
            $this->adapter->getFilesByType('docs', 'pdf')->all()
        );

        $this->assertArraysHaveSameValues(
            ['image.jpg', 'image2.jpg', 'docs/photo.jpg'],
            $this->adapter->getFilesByType('/', 'jpg', true)->all()
        );

        $files = $this->adapter->getFilesByTypeRecursively('/', 'jpg');
        $this->assertInstanceOf(FileCollection::class, $files);
        $this->assertArraysHaveSameValues(
            ['image.jpg', 'image2.jpg', 'docs/photo.jpg'],
            $files->all()
        );
    }

    #[Test]
    public function checks_for_empty_directories()
    {
        mkdir($this->tempDir.'/empty');
        mkdir($this->tempDir.'/full');
        file_put_contents($this->tempDir.'/full/filename.txt', '');
        $this->assertTrue($this->adapter->isEmpty('empty'));
        $this->assertFalse($this->adapter->isEmpty('full'));
    }

    #[Test]
    public function checks_for_directories()
    {
        mkdir($this->tempDir.'/directory');
        file_put_contents($this->tempDir.'/filename.txt', '');
        $this->assertTrue($this->adapter->isDirectory('directory'));
        $this->assertFalse($this->adapter->isDirectory('filename.txt'));
    }

    #[Test]
    public function copies_directories()
    {
        mkdir($this->tempDir.'/src');
        file_put_contents($this->tempDir.'/src/one.txt', 'One');
        file_put_contents($this->tempDir.'/src/two.txt', 'Two');

        $this->adapter->copyDirectory('src', 'dest');

        $this->assertStringEqualsFile($this->tempDir.'/src/one.txt', 'One');
        $this->assertStringEqualsFile($this->tempDir.'/src/two.txt', 'Two');
        $this->assertStringEqualsFile($this->tempDir.'/dest/one.txt', 'One');
        $this->assertStringEqualsFile($this->tempDir.'/dest/two.txt', 'Two');
    }

    #[Test]
    public function moves_directories()
    {
        mkdir($this->tempDir.'/src');
        file_put_contents($this->tempDir.'/src/one.txt', 'One');
        file_put_contents($this->tempDir.'/src/two.txt', 'Two');

        $this->adapter->moveDirectory('src', 'dest');

        $this->assertFileDoesNotExist($this->tempDir.'/src/one.txt');
        $this->assertFileDoesNotExist($this->tempDir.'/src/two.txt');
        $this->assertStringEqualsFile($this->tempDir.'/dest/one.txt', 'One');
        $this->assertStringEqualsFile($this->tempDir.'/dest/two.txt', 'Two');
    }

    #[Test]
    public function deletes_empty_subdirectories()
    {
        mkdir($this->tempDir.'/one/two', 0755, true);
        mkdir($this->tempDir.'/three/four', 0755, true);
        mkdir($this->tempDir.'/three/five/six', 0755, true);
        file_put_contents($this->tempDir.'/one/two/file.txt', '');
        file_put_contents($this->tempDir.'/three/file.txt', '');

        $this->adapter->deleteEmptySubfolders('/');

        $this->assertDirectoryExists($this->tempDir.'/one');
        $this->assertDirectoryExists($this->tempDir.'/one/two');
        $this->assertDirectoryExists($this->tempDir.'/three');
        $this->assertDirectoryDoesNotExist($this->tempDir.'/three/four');
        $this->assertDirectoryDoesNotExist($this->tempDir.'/three/five');
        $this->assertDirectoryDoesNotExist($this->tempDir.'/three/five/six');
    }

    #[Test]
    public function gets_filesystem()
    {
        $this->assertEquals($this->filesystem, $this->adapter->filesystem());
    }

    /**
     * Assert that two arrays have the same values but not necessarily in the same order.
     */
    private function assertArraysHaveSameValues($expected, $actual)
    {
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
}
