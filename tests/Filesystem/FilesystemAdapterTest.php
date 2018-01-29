<?php

namespace Tests\Filesystem;

use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Statamic\Filesystem\FilesystemAdapter;

class FilesystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    protected function makeAdapter()
    {
        return new FilesystemAdapter(
            $this->filesystem = new Filesystem,
            $this->tempDir
        );
    }
}
