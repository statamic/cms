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

        // Outside the skeleton, so descending into a link would be a genuine delete beyond it.
        // Not in the repo's tracked tree, so this test doesn't do what the trait exists to stop.
        // And on the same volume as the checkout: Filesystem::link() hard links the file case on
        // Windows, and hard links can't cross volumes, so a temp dir on another drive wouldn't be
        // linkable at all. dirname(base_path()) is the skeleton's parent, inside gitignored vendor.
        $this->target = dirname(base_path()).'/restores-testbench-skeleton-tmp';
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

        // Filesystem::link() shells out on Windows and throws away exec()'s result, so a link
        // that never got created would leave every assertion below passing on a path that
        // isn't there. Fail loudly instead of silently testing nothing.
        $this->assertDirectoryExists($linkedDir);
        $this->assertFileExists($linkedFile);

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
