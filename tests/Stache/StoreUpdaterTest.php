<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\StoreUpdater;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\BasicStore;

class StoreUpdaterTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!is_dir($this->tempDir = __DIR__.'/tmp')) {
            mkdir($this->tempDir);
        }

        $this->stache = (new StoreUpdaterTestStache)->sites(['en', 'es']);
        $this->store = new class($this->stache, app('files')) extends BasicStore {
            public function key() { return 'test-store-key'; }
            public function getItemsFromCache($cache) {
                return $cache->map(function ($item) {
                    return new class($item) {
                        protected $data;
                        public function __construct($data) { $this->data = $data; }
                        public function id() { return $this->data()['id']; }
                        public function data() { return $this->data; }
                        public function uri() { return $this->data()['uri']; }
                        public function toCacheableArray() { return $this->data(); }
                    };
                });
            }
            public function createItemFromFile($path, $contents) { return require($path); }
            public function getItemKey($item) { return $item->id(); }
        };
        $this->stache->registerStore($this->store);
        $this->updater = (new StoreUpdater($this->stache, new Filesystem))->store($this->store);
    }

    public function tearDown()
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_holds_traversed_files_in_a_property_to_prevent_multiple_traversals()
    {
        Traverser::shouldReceive('traverse')->once()->andReturn([]);

        $this->updater->files();
        $this->updater->files();
    }

    /** @test */
    function it_gets_timestamps_from_cache()
    {
        $cached = ['/one.txt' => 1234567890];

        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn($cached);

        $timestamps = $this->updater->timestamps();
        $this->assertInstanceOf(Collection::class, $timestamps);
        $this->assertEquals($cached, $timestamps->all());
    }

    /** @test */
    function it_gets_empty_collection_if_cache_is_missing()
    {
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturnNull();

        $timestamps = $this->updater->timestamps();
        $this->assertInstanceOf(Collection::class, $timestamps);
        $this->assertEquals([], $timestamps->all());
    }

    /** @test */
    function it_holds_timestamps_in_a_property_to_prevent_multiple_cache_calls()
    {
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->once()->andReturn([]);

        $this->updater->timestamps();
        $this->updater->timestamps();
    }

    /** @test */
    function it_gets_updated_files_based_on_timestamps()
    {
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn([
            '/untouched.txt' => now()->subDays(2)->timestamp,
            '/modified.txt' => now()->subDays(2)->timestamp,
            '/deleted.txt' => now()->subDays(2)->timestamp,
        ]);

        Traverser::shouldReceive('traverse')->once()->andReturn(collect([
            '/untouched.txt' => now()->subDays(2)->timestamp,
            '/modified.txt' => now()->subDays(1)->timestamp,
            '/new.txt' => now()->subDays(2)->timestamp,
        ]));

        $this->assertEquals([
            '/modified.txt',
            '/new.txt'
        ], $this->updater->modifiedFiles()->all());
    }

    /** @test */
    function it_gets_deleted_files_based_on_timestamps()
    {
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn([
            '/untouched.txt' => now()->subDays(2)->timestamp,
            '/modified.txt' => now()->subDays(2)->timestamp,
            '/deleted.txt' => now()->subDays(2)->timestamp,
        ]);

        Traverser::shouldReceive('traverse')->once()->andReturn(collect([
            '/untouched.txt' => now()->subDays(2)->timestamp,
            '/modified.txt' => now()->subDays(1)->timestamp,
            '/new.txt' => now()->subDays(2)->timestamp,
        ]));

        $this->assertEquals([
            '/deleted.txt',
        ], $this->updater->deletedFiles()->all());
    }

    /** @test */
    function updating_causes_a_store_to_get_loaded()
    {
        $this->stache->keys([]); // We don't care whats in the stache, just that it has something to loop over.
        $this->assertFalse($this->store->isLoaded());
        Traverser::shouldReceive('traverse')->andReturn(collect());

        $this->updater->update();

        $this->assertTrue($this->store->isLoaded());
    }

    /** @test */
    function it_updates_items_for_each_modified_file()
    {
        $contents = <<<'EOT'
<?php
return new class {
    public function id() { return $this->data()['id']; }
    public function data() { return ['title' => 'Item title updated', 'id' => '123', 'uri' => '/updated-uri']; }
    public function uri() { return $this->data()['uri']; }
    public function path() { return '/path'; }
    public function toCacheableArray() { return $this->data(); }
};
EOT;
        file_put_contents($this->tempDir.'/test.txt', $contents);
        touch($this->tempDir.'/test.txt', $existingTimestamp = now()->subdays(2)->timestamp);

        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn([
            $this->tempDir.'/test.txt' => $existingTimestamp,
        ]);

        Cache::shouldReceive('get')->with('stache::items/test-store-key')->andReturn([
            '123' => ['title' => 'Item title', 'id' => '123', 'uri' => '/existing-uri'],
        ]);

        Traverser::shouldReceive('traverse')->once()->andReturn(collect($traversedFiles = [
            $this->tempDir.'/test.txt' => now()->subDays(1)->timestamp,
        ]));

        // TODO: The caching of the items has been moved into the Persister class, which doesn't have any test coverage.
        // Cache::shouldReceive('forever')->once()->with('stache::items/test-store-key', \Mockery::any());
        // Cache::shouldReceive('forever')->once()->with('stache::meta/test-store-key', \Mockery::any());

        $this->assertEquals('Item title', $this->store->getItem('123')->data()['title']);

        $this->updater->update();

        $this->assertEquals($this->stache->queuedTimestampCaches()['stache::timestamps/test-store-key'], $traversedFiles);
        $this->assertEquals('Item title updated', $this->store->getItem('123')->data()['title']);
        $this->assertEquals('/updated-uri', $this->store->getSiteUri('en', '123'));
    }

    /** @test */
    function it_doesnt_add_uris_if_the_object_doesnt_have_uri_method()
    {
        $contents = <<<'EOT'
<?php
return new class {
    public function id() { return $this->data()['id']; }
    public function data() { return ['title' => 'Item title updated', 'id' => '123', 'uri' => '/updated-uri']; }
    public function path() { return '/path'; }

    // This method is explicitly *not* here.
    // public function uri() { }
};
EOT;
        file_put_contents($this->tempDir.'/test.txt', $contents);

        Traverser::shouldReceive('traverse')->once()->andReturn(collect($traversedFiles = [
            $this->tempDir.'/test.txt' => now()->subDays(1)->timestamp,
        ]));

        $this->updater->update();

        $this->assertNull($this->store->getSiteUri('en', '123'));
    }

    /** @test */
    function it_deletes_items_for_each_deleted_file()
    {
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn([
            $this->tempDir.'/deleted.txt' => now()->subdays(2)->timestamp,
        ]);
        Cache::shouldReceive('get')->with('stache::items/test-store-key')->andReturn([
            '123' => ['title' => 'Item title', 'id' => '123'],
        ]);
        Cache::shouldReceive('forever');
        Traverser::shouldReceive('traverse')->once()->andReturn(collect([]));
        $this->store->setSitePath('en', '123', $this->tempDir.'/deleted.txt');
        $this->assertEquals('Item title', $this->store->getItem('123')->data()['title']);
        $this->assertEquals(1, $this->store->getItemsWithoutLoading()->count());

        $this->updater->update();

        $this->assertNull($this->store->getItem('123'));
        $this->assertEquals(0, $this->store->getItemsWithoutLoading()->count());
    }

    /** @test */
    function timestamps_get_persisted()
    {
        $contents = <<<'EOT'
<?php
return new class {
    public function id() { return $this->data()['id']; }
    public function data() { return ['title' => 'Item title updated', 'id' => '123']; }
    public function uri() { return '/test'; }
    public function path() { return '/test'; }
    public function toCacheableArray() { return $this->data(); }
};
EOT;
        file_put_contents($this->tempDir.'/test.txt', $contents);
        Traverser::shouldReceive('traverse')->andReturn(collect([
            $this->tempDir.'/test.txt' => now()->subDays(1)->timestamp,
        ]));
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn([]);
        Cache::shouldReceive('get')->with('stache::items/test-store-key')->andReturn([]);

        $this->updater->update();

        $this->assertEquals([
            $this->tempDir.'/test.txt' => now()->subDays(1)->timestamp,
        ], $this->stache->queuedTimestampCaches()['stache::timestamps/test-store-key']);
    }

    /** @test */
    function nothing_should_get_cached_if_there_are_no_changes()
    {
        touch($this->tempDir.'/test.txt', $timestamp = now()->subDays(1)->timestamp);
        Traverser::shouldReceive('traverse')->andReturn(collect([
            $this->tempDir.'/test.txt' => $timestamp,
        ]));
        Cache::shouldReceive('get')->with('stache::timestamps/test-store-key', [])->andReturn([
            $this->tempDir.'/test.txt' => $timestamp,
        ]);
        Cache::shouldReceive('get')->with('stache::items/test-store-key')->andReturn([]);

        Cache::shouldNotReceive('forever');

        $this->updater->update();
    }
}

class StoreUpdaterTestStache extends Stache
{
    public $timestamps;

    public function queueTimestampCache($key, $timestamps)
    {
        $this->timestamps[$key] = $timestamps;
    }
}
