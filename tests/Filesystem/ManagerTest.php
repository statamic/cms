<?php

namespace Tests\Filesystem;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\File;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    /** @test */
    public function it_wraps_an_illuminate_disk()
    {
        $illuminate = Storage::fake('test');

        $disk = File::disk($illuminate);

        $this->assertInstanceOf(FlysystemAdapter::class, $disk);
        $this->assertSame($illuminate, $disk->filesystem());
    }
}
