<?php

namespace Tests\Stache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Stache\StacheFileStore;
use Tests\TestCase;

class StacheFileStoreTest extends TestCase
{
    private $dir;

    public function setUp(): void
    {
        parent::setUp();

        $this->dir = __DIR__.'/tmp-stache-cache';
    }

    public function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->dir);

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('pathProvider')]
    public function it_groups_keys_into_directories_by_their_prefix($key, $directory)
    {
        $store = new StacheFileStore(new Filesystem, $this->dir);

        $this->assertEquals($this->dir.'/'.$directory.'/'.sha1($key), $store->path($key));
    }

    public static function pathProvider()
    {
        return [
            'item' => ['stache::items::entries::articles::123', 'stache.items.entries.articles'],
            'item with slashes' => ['stache::items::assets::main::foo/bar.jpg', 'stache.items.assets.main'],
            'index' => ['stache::indexes::entries::articles::title', 'stache.indexes.entries.articles'],
            'timestamps' => ['stache::timestamps::entries::articles', 'stache.timestamps.entries'],
            'no prefix' => ['stache::timing', 'stache'],
            'no separator' => ['foo', '_'],
            'unsafe characters' => ['stache::keys/foo bar::baz', 'stache.keys.foo.bar'],
        ];
    }

    #[Test]
    public function items_in_the_same_store_share_a_directory()
    {
        $store = new StacheFileStore(new Filesystem, $this->dir);

        $this->assertEquals(
            dirname($store->path('stache::items::entries::articles::one')),
            dirname($store->path('stache::items::entries::articles::two'))
        );

        $this->assertNotEquals(
            dirname($store->path('stache::items::entries::articles::one')),
            dirname($store->path('stache::items::entries::pages::one'))
        );
    }

    #[Test]
    public function it_can_be_used_as_a_cache_driver()
    {
        config(['cache.stores.stache-test' => ['driver' => 'stache', 'path' => $this->dir]]);

        $cache = Cache::store('stache-test');

        $this->assertInstanceOf(StacheFileStore::class, $cache->getStore());

        $cache->forever('stache::items::entries::articles::123', ['title' => 'Hello']);
        $cache->put('stache::items::entries::articles::456', 'World', 60);

        $this->assertFileExists($this->dir.'/stache.items.entries.articles/'.sha1('stache::items::entries::articles::123'));
        $this->assertEquals(['title' => 'Hello'], $cache->get('stache::items::entries::articles::123'));
        $this->assertEquals('World', $cache->get('stache::items::entries::articles::456'));

        $cache->forget('stache::items::entries::articles::123');
        $this->assertNull($cache->get('stache::items::entries::articles::123'));

        $cache->flush();
        $this->assertNull($cache->get('stache::items::entries::articles::456'));
        $this->assertEmpty((new Filesystem)->directories($this->dir));
    }

    #[Test]
    public function the_stache_can_use_it()
    {
        config([
            'cache.stores.stache-test' => ['driver' => 'stache', 'path' => $this->dir],
            'statamic.stache.cache_store' => 'stache-test',
        ]);

        \Statamic\Facades\Stache::cacheStore()->forever('stache::items::entries::articles::123', 'foo');

        $this->assertFileExists($this->dir.'/stache.items.entries.articles/'.sha1('stache::items::entries::articles::123'));
    }
}
