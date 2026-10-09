<?php

namespace Tests\Forms;

use Illuminate\Filesystem\FilesystemAdapter;

// Wraps a real local Flysystem backend but makes path() lie, simulating a
// cloud disk (S3 etc.) where path() doesn't resolve to a real local file.
class NonLocalPathFilesystemAdapter extends FilesystemAdapter
{
    public function path($path)
    {
        return $path;
    }
}
