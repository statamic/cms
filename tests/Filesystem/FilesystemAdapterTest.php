<?php

namespace Tests\Filesystem;

use Mockery;
use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Statamic\Filesystem\FilesystemAdapter;

class FilesystemAdapterTest extends \PHPUnit\Framework\TestCase
{
    private $mock;

    public function setUp()
    {
        parent::setUp();

        $this->mock = Mockery::mock(Filesystem::class);
        $this->adapter = new FilesystemAdapter($this->mock, '/path/to');
    }

    /** @test */
    function gets_file_contents()
    {
        $this->mock->shouldReceive('exists')->with('/path/to/filename.txt')->andReturnTrue();
        $this->mock->shouldReceive('get')->with('/path/to/filename.txt')->andReturn('bar');

        $this->assertEquals('bar', $this->adapter->get('filename.txt'));
    }

    /** @test */
    function gets_fallback_if_file_doesnt_exist()
    {
        $this->mock->shouldReceive('exists')->with('/path/to/filename.txt')->andReturnFalse();

        $this->assertEquals('baz', $this->adapter->get('filename.txt', 'baz'));
    }

    /** @test */
    function checks_if_file_exists()
    {
        $this->mock->shouldReceive('exists')->with('/path/to/filename.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->exists('filename.txt'));
    }

    /** @test */
    function puts_contents_into_a_file()
    {
        $this->mock->shouldReceive('makeDirectory')->with('/path/to', 0755, true, true)->andReturnTrue();
        $this->mock->shouldReceive('put')->with('/path/to/filename.txt', 'bar')->andReturnTrue();

        $this->assertTrue($this->adapter->put('filename.txt', 'bar'));
    }

    /** @test */
    function deletes_files()
    {
        $this->mock->shouldReceive('delete')->with('/path/to/filename.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->delete('filename.txt'));
    }

    /** @test */
    function copies_files()
    {
        $this->mock->shouldReceive('copy')->with('/path/to/src.txt', '/path/to/dest.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->copy('src.txt', 'dest.txt'));
    }

    /** @test */
    function copies_files_and_overwrites()
    {
        $this->mock->shouldReceive('exists')->with('/path/to/dest.txt')->andReturnTrue();
        $this->mock->shouldReceive('delete')->with('/path/to/dest.txt')->andReturnTrue();
        $this->mock->shouldReceive('copy')->with('/path/to/src.txt', '/path/to/dest.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->copy('src.txt', 'dest.txt'));
    }

    /** @test */
    function moves_files()
    {
        $this->mock->shouldReceive('move')->with('/path/to/src.txt', '/path/to/dest.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->move('src.txt', 'dest.txt'));
    }

    /** @test */
    function moves_files_and_overwrites()
    {
        $this->mock->shouldReceive('exists')->with('/path/to/dest.txt')->andReturnTrue();
        $this->mock->shouldReceive('delete')->with('/path/to/dest.txt')->andReturnTrue();
        $this->mock->shouldReceive('move')->with('/path/to/src.txt', '/path/to/dest.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->move('src.txt', 'dest.txt'));
    }

    /** @test */
    function renames_a_file()
    {
        $this->mock->shouldReceive('move')->with('/path/to/src.txt', '/path/to/dest.txt')->andReturnTrue();

        $this->assertTrue($this->adapter->rename('src.txt', 'dest.txt'));
    }

    /** @test */
    function gets_file_extension()
    {
        $this->assertEquals('jpg', $this->adapter->extension('photo.jpg'));
    }

    /** @test */
    function gets_mime_type()
    {
        $this->mock->shouldReceive('mimeType')->with('/path/to/test.json')->andReturn('application/json');

        $this->assertEquals('application/json', $this->adapter->mimeType('test.json'));
    }

    /** @test */
    function gets_last_modified()
    {
        $this->mock->shouldReceive('lastModified')->with('/path/to/filename.txt')->andReturn(12345);

        $this->assertEquals(12345, $this->adapter->lastModified('filename.txt'));
    }

    /** @test */
    function gets_file_size()
    {
        $this->mock->shouldReceive('size')->with('/path/to/filename.txt')->andReturn(5678);

        $this->assertEquals(5678, $this->adapter->size('filename.txt'));
    }

    /** @test */
    function gets_file_size_for_humans()
    {
        // $this->mock->shouldReceive('size')->with('/path/to/filename.txt')->andReturn(10270);
        // Str::shouldReceive('fileSizeForHumans')->andReturn('10 MB');

        // $this->assertEquals('10 MB', $this->adapter->sizeHuman('filename.txt'));
    }

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
        $this->mock->shouldReceive('makeDirectory')->with('/path/to/directory', 0755, true, true)->andReturnTrue();

        $this->assertTrue($this->adapter->makeDirectory('directory'));
    }
}
