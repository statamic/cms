<?php

namespace Tests;

use FilesystemIterator;

trait DeletesDirectories
{
    // Laravel's deleteDirectory() reaches for unlink(), which on Windows cannot remove
    // anything carrying the directory attribute. Symlinked and junctioned directories
    // survive it, along with every directory above them.
    protected function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS) as $item) {
            $path = $item->getPathname();

            // Deliberately no is_dir()/is_link() calls. A junction reports an lstat
            // mode that is neither, and PHP caches an lstat result as the stat result
            // when it decides the path isn't a link, so the two answers contradict
            // each other. unlink() removes files and file symlinks, rmdir() removes
            // empty directories, directory symlinks and junctions without touching
            // what they point at, and anything surviving both has contents in it.
            if (@unlink($path) || @rmdir($path)) {
                continue;
            }

            $this->deleteDirectory($path);
        }

        @rmdir($directory);
    }
}
