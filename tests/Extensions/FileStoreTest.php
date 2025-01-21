<?php

namespace Tests\Extensions;

use Illuminate\Cache\ArrayStore;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extensions\FileStore;
use Tests\TestCase;

class FileStoreTest extends TestCase
{
    #[Test]
    #[DefineEnvironment('cache')]
    public function it_overrides_file_driven_stores()
    {
        $alfa = Cache::store('alfa')->getStore();
        $this->assertInstanceOf(FileStore::class, $alfa);
        $this->assertEquals(storage_path('framework/cache/alfa'), $alfa->getDirectory());

        $bravo = Cache::store('bravo')->getStore();
        $this->assertInstanceOf(FileStore::class, $bravo);
        $this->assertEquals(storage_path('framework/cache/bravo'), $bravo->getDirectory());

        // Non-file stores shouldn't be modified.
        $charlie = Cache::store('charlie')->getStore();
        $this->assertInstanceOf(ArrayStore::class, $charlie);
    }

    public function cache($app)
    {
        $app['config']->set('cache.stores.alfa', [
            'driver' => 'file',
            'path' => storage_path('framework/cache/alfa'),
        ]);

        $app['config']->set('cache.stores.bravo', [
            'driver' => 'file',
            'path' => storage_path('framework/cache/bravo'),
        ]);

        $app['config']->set('cache.stores.charlie', [
            'driver' => 'array',
        ]);
    }
}
