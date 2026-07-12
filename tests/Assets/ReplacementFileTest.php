<?php

namespace Tests\Assets;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\ReplacementFile;
use Tests\TestCase;

class ReplacementFileTest extends TestCase
{
    #[Test]
    public function it_gets_the_path_and_extension()
    {
        $file = new ReplacementFile('foo/bar/baz.jpg');
        $this->assertEquals('foo/bar/baz.jpg', $file->path());
        $this->assertEquals('jpg', $file->extension());
    }

    #[Test]
    public function it_writes_the_file_to_another_disk()
    {
        $originDisk = Storage::fake('local');
        $targetDisk = Storage::fake('target');
        $originDisk->write('foo/bar/baz.jpg', 'contents');

        $targetDisk->assertMissing('the/new/path.jpg');

        (new ReplacementFile('foo/bar/baz.jpg'))->writeTo($targetDisk, 'the/new/path.jpg');

        $targetDisk->assertExists('the/new/path.jpg');
    }
}
