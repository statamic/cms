<?php

namespace Tests\Filesystem;

use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Statamic\Filesystem\FilesystemAdapter;

class FilesystemAdapterTest extends \PHPUnit\Framework\TestCase
{
    use FilesystemAdapterTests;

    protected function makeAdapter()
    {
        return new FilesystemAdapter(new Filesystem, $this->tempDir);
    }
}
