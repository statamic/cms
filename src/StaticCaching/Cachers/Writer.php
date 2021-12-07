<?php

namespace Statamic\StaticCaching\Cachers;

use Statamic\Facades\File;
use Statamic\Facades\Folder;

class Writer
{
    /**
     * Write the cache file to disk.
     *
     * @param  string  $path  The path to the file.
     * @param  string  $content  The content of the file.
     * @param  int  $lockFor
     * @return bool True if written, false if not.
     */
    public function write($path, $content, $lockFor = 0)
    {
        @mkdir(dirname($path), 0777, true);

        // Create the file handle. We use the "c" mode which will avoid writing an
        // empty file if we abort when checking the lock status in the next step.
        $handle = fopen($path, 'c');

        // Attempt to obtain the lock for a the file. If the file is already locked, then we'll
        // abort right here since another process is in the middle of writing the file. Since
        // file locks are only advisory, we'll have to manually check and prevent writing.
        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            return false;
        }

        fwrite($handle, $content);
        chmod($path, 0777);

        // Hold the file lock for a moment to prevent other processes from trying to write the same file.
        sleep($lockFor);

        fclose($handle);

        return true;
    }

    /**
     * Delete the cache file from disk.
     *
     * @param  string  $path
     * @return bool
     */
    public function delete($path)
    {
        if (! File::exists($path)) {
            return false;
        }

        File::delete($path);

        return true;
    }

    public function flush($path)
    {
        if (! File::exists($path)) {
            return;
        }

        foreach (Folder::getFilesRecursively($path) as $file) {
            File::delete($file);
        }

        Folder::deleteEmptySubfolders($path);
    }
}
