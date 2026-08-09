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

            if (is_link($path)) {
                @unlink($path) || @rmdir($path);

                continue;
            }

            if (! is_dir($path)) {
                @unlink($path);

                continue;
            }

            // A junction has neither a link nor a directory lstat mode, so it is
            // indistinguishable from a directory here. rmdir() removes an empty
            // directory or a junction, and leaves the junction's target alone.
            if (! @rmdir($path)) {
                $this->deleteDirectory($path);
            }
        }

        @rmdir($directory);
    }
}
