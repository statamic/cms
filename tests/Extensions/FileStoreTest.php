<?php

namespace Tests\Extensions;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore as LaravelFileStore;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extensions\FileStore;
use Tests\TestCase;

class FileStoreTest extends TestCase
{
    private $dir;

    public function setUp(): void
    {
        parent::setUp();

        $this->dir = __DIR__.'/tmp-file-store';
    }

    public function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->dir);
        Carbon::setTestNow();

        parent::tearDown();
    }

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

    public static function keyProvider()
    {
        return [
            'stache key' => ['stache::items::entries::articles::'],
            'other key' => ['other-'],
        ];
    }

    #[Test]
    #[DataProvider('keyProvider')]
    public function it_reads_values($prefix)
    {
        $store = new FileStore(new Filesystem, $this->dir);

        $store->forever($prefix.'array', ['foo' => 'bar']);
        $store->forever($prefix.'false', false);
        $store->forever($prefix.'null', null);
        $store->put($prefix.'expiring', 'baz', 60);

        $this->assertEquals(['foo' => 'bar'], $store->get($prefix.'array'));
        $this->assertFalse($store->get($prefix.'false'));
        $this->assertNull($store->get($prefix.'null'));
        $this->assertEquals('baz', $store->get($prefix.'expiring'));
        $this->assertNull($store->get($prefix.'missing'));
        $this->assertEquals(
            [$prefix.'array' => ['foo' => 'bar'], $prefix.'missing' => null],
            $store->many([$prefix.'array', $prefix.'missing'])
        );
    }

    #[Test]
    #[DataProvider('keyProvider')]
    public function expired_values_are_forgotten($prefix)
    {
        $store = new FileStore(new Filesystem, $this->dir);

        Carbon::setTestNow(Carbon::parse('2025-01-01 00:00:00'));
        $store->put($prefix.'expiring', 'baz', 60);
        $store->forever($prefix.'forever', 'qux');

        Carbon::setTestNow(Carbon::parse('2025-01-01 00:01:00'));

        $this->assertNull($store->get($prefix.'expiring'));
        $this->assertFileDoesNotExist($store->path($prefix.'expiring'));
        $this->assertEquals('qux', $store->get($prefix.'forever'));
    }

    #[Test]
    #[DataProvider('corruptFileProvider')]
    public function corrupt_files_are_forgotten($contents)
    {
        $store = new FileStore(new Filesystem, $this->dir);
        $path = $store->path('stache::items::entries::articles::corrupt');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, $contents);

        $this->assertNull($store->get('stache::items::entries::articles::corrupt'));
        $this->assertFileDoesNotExist($path);
    }

    public static function corruptFileProvider()
    {
        return [
            'empty' => [''],
            'only part of the expiration' => ['99999'],
            'truncated value' => ['9999999999'.substr(serialize(['foo' => str_repeat('bar', 100)]), 0, 50)],
        ];
    }

    #[Test]
    public function it_only_unserializes_allowed_classes_when_configured()
    {
        if (! method_exists(LaravelFileStore::class, 'unserialize')) {
            $this->markTestSkipped('This version of Laravel does not support restricting unserializable classes.');
        }

        $store = new FileStore(new Filesystem, $this->dir, null, []);
        $store->forever('stache::items::entries::articles::object', new \ArrayObject(['foo' => 'bar']));

        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $store->get('stache::items::entries::articles::object'));
    }
}
