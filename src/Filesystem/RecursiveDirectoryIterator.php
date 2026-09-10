<?php

namespace Statamic\Filesystem;

class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    public function hasChildren(bool $allowLinks = false): bool
    {
        if (parent::hasChildren($allowLinks)) {
            return true;
        }

        if (DIRECTORY_SEPARATOR !== '\\') {
            return false;
        }

        // A Windows junction reports an lstat mode that is neither a link nor a
        // directory, so the parent treats it as a leaf and FOLLOW_SYMLINKS never
        // gets a look in. The parent has just lstat'd the path, and is_dir() would
        // reuse that cached result, so the cache needs clearing before asking.
        clearstatcache(true, $path = $this->getPathname());

        return is_dir($path);
    }
}
