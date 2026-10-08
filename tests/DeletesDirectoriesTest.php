<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;

class DeletesDirectoriesTest extends TestCase
{
    use DeletesDirectories;

    private string $dir;

    public function setUp(): void
    {
        parent::setUp();

        $this->dir = __DIR__.'/deletes-directories-tmp';
    }

    public function tearDown(): void
    {
        $this->deleteDirectory($this->dir);

        parent::tearDown();
    }

    #[Test]
    public function it_deletes_a_directory_containing_links_without_touching_their_targets()
    {
        File::put($this->dir.'/target-dir/kept.html', '');
        File::put($this->dir.'/target-file.html', '');

        File::put($this->dir.'/subject/file.html', '');
        File::put($this->dir.'/subject/nested/deep.html', '');
        File::makeDirectory($this->dir.'/subject/empty');

        // A directory link is a junction on Windows, where neither unlink() nor an
        // is_dir()/is_link() check behaves the way it does everywhere else.
        app('files')->link($this->dir.'/target-dir', $this->dir.'/subject/linked-dir');
        app('files')->link($this->dir.'/target-file.html', $this->dir.'/subject/linked-file.html');

        $this->deleteDirectory($this->dir.'/subject');

        clearstatcache();

        $this->assertFalse(is_dir($this->dir.'/subject'));
        $this->assertTrue(is_file($this->dir.'/target-dir/kept.html'));
        $this->assertTrue(is_file($this->dir.'/target-file.html'));
    }
}
