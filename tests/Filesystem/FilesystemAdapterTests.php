<?php

namespace Tests\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Statamic\Filesystem\FilesystemAdapter;

trait FilesystemAdapterTests
{
    public function setUp()
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->adapter = $this->makeAdapter();
    }

    public function tearDown()
    {
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function gets_file_contents()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertEquals('Hello World', $this->adapter->get('filename.txt'));
    }

    /** @test */
    function gets_fallback_if_file_doesnt_exist()
    {
        $this->assertEquals('Hello World', $this->adapter->get('filename.txt', 'Hello World'));
    }

    /** @test */
    function checks_if_file_exists()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertTrue($this->adapter->exists('filename.txt'));
        $this->assertFalse($this->adapter->exists('another.txt'));
    }

    /** @test */
    function assumes_existence_if_checking_on_the_root()
    {
        $this->assertTrue($this->adapter->exists());
    }

    /** @test */
    function puts_contents_into_a_file()
    {
        $this->adapter->put('filename.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/filename.txt', 'Hello World');
    }

    /** @test */
    function deletes_files()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->adapter->delete('filename.txt');
        $this->assertFileNotExists($this->tempDir.'/filename.txt');
    }

    /** @test */
    function copies_files()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        $this->assertTrue($this->adapter->copy('src.txt', 'dest.txt'));
        $this->assertFileExists($this->tempDir.'/dest.txt');
        $this->assertFileExists($this->tempDir.'/src.txt');
    }

    /** @test */
    function copies_files_and_overwrites()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        file_put_contents($this->tempDir.'/dest.txt', 'Existing Content');
        $this->assertTrue($this->adapter->copy('src.txt', 'dest.txt', true));
        $this->assertStringEqualsFile($this->tempDir.'/src.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
    }

    /** @test */
    function moves_files()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        $this->assertTrue($this->adapter->move('src.txt', 'dest.txt'));
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
        $this->assertFileNotExists($this->tempDir.'/src.txt');
    }

    /** @test */
    function moves_files_and_overwrites()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        file_put_contents($this->tempDir.'/dest.txt', 'Existing Content');
        $this->assertTrue($this->adapter->move('src.txt', 'dest.txt', true));
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
        $this->assertFileNotExists($this->tempDir.'/src.txt');
    }

    /** @test */
    function renames_a_file()
    {
        file_put_contents($this->tempDir.'/src.txt', 'Hello World');
        $this->assertTrue($this->adapter->rename('src.txt', 'dest.txt'));
        $this->assertFileNotExists($this->tempDir.'/src.txt');
        $this->assertStringEqualsFile($this->tempDir.'/dest.txt', 'Hello World');
    }

    /** @test */
    function gets_file_extension()
    {
        $this->assertEquals('jpg', $this->adapter->extension('photo.jpg'));
    }

    /** @test */
    function gets_mime_type()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertEquals('text/plain', $this->adapter->mimeType('filename.txt'));
    }

    /** @test */
    function gets_last_modified()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        touch($this->tempDir.'/filename.txt', $time = 1512160249);
        $this->assertEquals($time, $this->adapter->lastModified('filename.txt'));
    }

    /** @test */
    function gets_file_size()
    {
        file_put_contents($this->tempDir.'/filename.txt', 'Hello World');
        $this->assertEquals(11, $this->adapter->size('filename.txt'));
    }

    // /** @test */
    // function gets_file_size_for_humans()
    // {
    //     // todo
    // }

    /** @test */
    function checks_if_a_file_is_an_image()
    {
        $this->assertTrue($this->adapter->isImage('test.jpg'));
        $this->assertTrue($this->adapter->isImage('test.jpeg'));
        $this->assertTrue($this->adapter->isImage('test.png'));
        $this->assertTrue($this->adapter->isImage('test.gif'));
        $this->assertTrue($this->adapter->isImage('test.JPG'));
        $this->assertTrue($this->adapter->isImage('test.JPEG'));
        $this->assertTrue($this->adapter->isImage('test.PNG'));
        $this->assertTrue($this->adapter->isImage('test.GIF'));
        $this->assertFalse($this->adapter->isImage('test.txt'));
    }

    /** @test */
    function makes_a_directory()
    {
        $this->assertTrue($this->adapter->makeDirectory('directory'));
        $this->assertDirectoryExists($this->tempDir.'/directory');
    }

    /** @test */
    function gets_files_from_a_directory()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');

        $this->assertArraysHaveSameValues(
            ['sub/two.txt', 'sub/three.txt'],
            $this->adapter->getFiles('sub')
        );

        $this->assertEquals([], $this->adapter->getFiles('non-existent-directory'));
    }

    /** @test */
    function gets_files_from_a_directory_recursively()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');

        $expected = ['sub/two.txt', 'sub/three.txt', 'sub/sub/four.txt'];
        $this->assertArraysHaveSameValues($expected, $this->adapter->getFiles('sub', true));
        $this->assertArraysHaveSameValues($expected, $this->adapter->getFilesRecursively('sub'));
    }

    /** @test */
    function gets_files_recursively_with_directory_exceptions()
    {
        mkdir($this->tempDir.'/sub/sub', 0755, true);
        mkdir($this->tempDir.'/sub/exclude', 0755, true);
        file_put_contents($this->tempDir.'/one.txt', '');
        file_put_contents($this->tempDir.'/sub/two.txt', '');
        file_put_contents($this->tempDir.'/sub/three.txt', '');
        file_put_contents($this->tempDir.'/sub/sub/four.txt', '');
        file_put_contents($this->tempDir.'/sub/exclude/five.txt', '');

        $this->assertArraysHaveSameValues(
            ['sub/two.txt', 'sub/three.txt', 'sub/sub/four.txt'],
            $this->adapter->getFilesRecursivelyExcept('sub', ['exclude'])
        );
    }

    /** @test */
    function gets_folders()
    {
        mkdir($this->tempDir.'/foo');
        mkdir($this->tempDir.'/foo/bar');
        mkdir($this->tempDir.'/foo/baz');
        mkdir($this->tempDir.'/foo/bar/qux');
        mkdir($this->tempDir.'/baz');
        mkdir($this->tempDir.'/baz/foo');

        $this->assertArraysHaveSameValues(
            ['foo/bar', 'foo/baz'],
            $this->adapter->getFolders('foo')
        );
    }

    /** @test */
    function gets_folders_recursively()
    {
        mkdir($this->tempDir.'/foo');
        mkdir($this->tempDir.'/foo/bar');
        mkdir($this->tempDir.'/foo/baz');
        mkdir($this->tempDir.'/foo/bar/qux');
        mkdir($this->tempDir.'/baz');
        mkdir($this->tempDir.'/baz/foo');

        $this->assertArraysHaveSameValues(
            ['foo/bar', 'foo/baz', 'foo/bar/qux'],
            $this->adapter->getFoldersRecursively('foo')
        );
    }

    /** @test */
    function gets_files_by_type()
    {
        mkdir($this->tempDir.'/docs');
        file_put_contents($this->tempDir.'/image.jpg', '');
        file_put_contents($this->tempDir.'/image2.jpg', '');
        file_put_contents($this->tempDir.'/text.txt', '');
        file_put_contents($this->tempDir.'/docs/word.doc', '');
        file_put_contents($this->tempDir.'/docs/test.pdf', '');
        file_put_contents($this->tempDir.'/docs/photo.jpg', '');

        $this->assertArraysHaveSameValues(
            ['image.jpg', 'image2.jpg'],
            $this->adapter->getFilesByType('/', 'jpg')
        );

        $this->assertArraysHaveSameValues(
            ['docs/test.pdf'],
            $this->adapter->getFilesByType('docs', 'pdf')
        );

        $this->assertArraysHaveSameValues(
             ['image.jpg', 'image2.jpg', 'docs/photo.jpg'],
            $this->adapter->getFilesByType('/', 'jpg', true)
        );

        $this->assertArraysHaveSameValues(
             ['image.jpg', 'image2.jpg', 'docs/photo.jpg'],
            $this->adapter->getFilesByTypeRecursively('/', 'jpg')
        );
    }

    /** @test */
    function checks_for_empty_directories()
    {
        mkdir($this->tempDir.'/empty');
        mkdir($this->tempDir.'/full');
        file_put_contents($this->tempDir.'/full/filename.txt', '');
        $this->assertTrue($this->adapter->isEmpty('empty'));
        $this->assertFalse($this->adapter->isEmpty('full'));
    }

    /** @test */
    function checks_for_directories()
    {
        mkdir($this->tempDir.'/directory');
        file_put_contents($this->tempDir.'/filename.txt', '');
        $this->assertTrue($this->adapter->isDirectory('directory'));
        $this->assertFalse($this->adapter->isDirectory('filename.txt'));
    }

    /** @test */
    function copies_directories()
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

    /** @test */
    function moves_directories()
    {
        mkdir($this->tempDir.'/src');
        file_put_contents($this->tempDir.'/src/one.txt', 'One');
        file_put_contents($this->tempDir.'/src/two.txt', 'Two');

        $this->adapter->moveDirectory('src', 'dest');

        $this->assertFileNotExists($this->tempDir.'/src/one.txt');
        $this->assertFileNotExists($this->tempDir.'/src/two.txt');
        $this->assertStringEqualsFile($this->tempDir.'/dest/one.txt', 'One');
        $this->assertStringEqualsFile($this->tempDir.'/dest/two.txt', 'Two');
    }

    /** @test */
    function deletes_empty_subdirectories()
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
        $this->assertDirectoryNotExists($this->tempDir.'/three/four');
        $this->assertDirectoryNotExists($this->tempDir.'/three/five');
        $this->assertDirectoryNotExists($this->tempDir.'/three/five/six');
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
