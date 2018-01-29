<?php

namespace Tests\Filesystem;

use Tests\TestCase;
use League\Flysystem\Adapter\Local;
use Statamic\Filesystem\FlysystemAdapter;
use Statamic\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem as Flysystem;
use Illuminate\Filesystem\FilesystemAdapter as IlluminateFilesystemAdapter;

class FlysystemAdapterTest extends TestCase
{
    use FilesystemAdapterTests;

    protected function makeAdapter()
    {
        // Equivalent to `Storage::disk()`
        $this->filesystem = new IlluminateFilesystemAdapter(
            new Flysystem(new Local($this->tempDir))
        );

        return new FlysystemAdapter($this->filesystem);
    }
}
