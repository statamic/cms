<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;

class RestoresTestbenchSkeletonTest extends TestCase
{
    private string $target;

    public function setUp(): void
    {
        parent::setUp();

        $this->target = __DIR__.'/restores-testbench-skeleton-tmp';
    }

    public function tearDown(): void
    {
        $this->deleteDirectory($this->target);

        parent::tearDown();
    }

    #[Test]
    public function it_removes_files_and_directories_a_test_added()
    {
        File::put($file = base_path('added.html'), '');
        File::put($nested = base_path('added-dir/nested/deep.html'), '');

        $this->restoreTestbenchSkeleton();

        clearstatcache();

        $this->assertFileDoesNotExist($file);
        $this->assertFileDoesNotExist($nested);
        $this->assertDirectoryDoesNotExist(base_path('added-dir'));
    }

    #[Test]
    public function it_removes_links_without_following_them()
    {
        File::put($this->target.'/kept.html', '');
        File::put($targetFile = $this->target.'/kept-file.html', '');

        app('files')->link($this->target, $linkedDir = base_path('linked-dir'));
        app('files')->link($targetFile, $linkedFile = base_path('linked-file.html'));

        $this->restoreTestbenchSkeleton();

        clearstatcache();

        $this->assertFalse(is_link($linkedDir));
        $this->assertDirectoryDoesNotExist($linkedDir);
        $this->assertFalse(is_link($linkedFile));
        $this->assertFileDoesNotExist($linkedFile);

        // If the scan had descended into the link, the restore would have deleted the
        // target's contents along with it.
        $this->assertFileExists($this->target.'/kept.html');
        $this->assertFileExists($targetFile);
    }
}
