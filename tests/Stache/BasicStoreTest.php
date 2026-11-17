<?php

namespace Tests\Stache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Str;
use Tests\TestCase;

class BasicStoreTest extends TestCase
{
    private $tempDir;
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        @mkdir($this->tempDir = Path::tidy(__DIR__.'/tmp'));
        $this->store = (new TestBasicStore)->directory($this->tempDir);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    #[Test]
    public function it_gets_an_item_by_key()
    {
        file_put_contents($this->tempDir.'/foo.yaml', '');

        $item = $this->store->getItem('foo');
        $this->assertEquals('foo', $item->id());

        $this->assertNull($this->store->getItem('unknown'));
    }

    #[Test]
    public function items_are_different_instances_every_time()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        config()->set('cache.serializable_classes', [TestBasicStoreItem::class]);
        \Illuminate\Support\Facades\Cache::clear();

        file_put_contents($this->tempDir.'/foo.yaml', '');

        $this->assertNotNull($one = $this->store->getItem('foo'));
        $this->assertNotNull($two = $this->store->getItem('foo'));
        $this->assertNotSame($one, $two);
    }

    #[Test]
    public function it_gets_an_item_by_path()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_forgets_an_item_by_key()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_saves_an_item()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function clearing_forgets_cached_items()
    {
        file_put_contents($this->tempDir.'/foo.yaml', '');
        $this->store->getItem('foo');
        $this->assertTrue(Cache::has('stache::items::test::foo'));

        $this->store->clear();

        $this->assertFalse(Cache::has('stache::items::test::foo'));
    }

    /**
     * @see https://github.com/statamic/cms/issues/10295
     */
    #[Test]
    public function clearing_a_cold_store_does_not_read_any_files()
    {
        file_put_contents($this->tempDir.'/foo.yaml', '');
        file_put_contents($this->tempDir.'/bar.yaml', '');

        $this->store->clear();

        $this->assertEquals(0, $this->store->madeItemCount);
    }

    #[Test]
    public function rebuilding_paths_caches_file_timestamps_for_the_watcher()
    {
        config(['statamic.stache.watcher' => true]);

        file_put_contents($this->tempDir.'/foo.yaml', '');
        touch($this->tempDir.'/foo.yaml', 1234567890);

        $this->store->paths();
        $this->store->clear();
        $this->assertFalse(Cache::has('stache::timestamps::test'));

        $this->store->paths();

        $this->assertEquals([$this->tempDir.'/foo.yaml' => 1234567890], Cache::get('stache::timestamps::test'));
    }

    #[Test]
    public function the_watcher_removes_an_item_whose_file_was_deleted()
    {
        config(['statamic.stache.watcher' => true]);

        file_put_contents($this->tempDir.'/foo.yaml', '');
        file_put_contents($this->tempDir.'/bar.yaml', '');
        $this->store->paths();

        unlink($this->tempDir.'/bar.yaml');
        $store = $this->nextRequest();

        $this->assertNull($store->getItem('bar'));
        $this->assertEquals(['foo'], $store->paths()->keys()->all());
        $this->assertEquals(['foo'], $store->index('path')->keys());
    }

    #[Test]
    public function the_watcher_keeps_an_item_whose_file_was_renamed()
    {
        config(['statamic.stache.watcher' => true]);

        $this->store = (new TestBasicStoreWithIds)->directory($this->tempDir);
        file_put_contents($this->tempDir.'/a.yaml', "id: one\ntitle: First");
        $this->store->paths();

        rename($this->tempDir.'/a.yaml', $this->tempDir.'/b.yaml');
        $store = $this->nextRequest();

        $this->assertNotNull($item = $store->getItem('one'));
        $this->assertEquals('First', $item->data()['title']);
        $this->assertEquals(['one' => $this->tempDir.'/b.yaml'], $store->paths()->all());
    }

    #[Test]
    public function the_watcher_handles_a_deleted_and_an_added_file_in_the_same_request()
    {
        config(['statamic.stache.watcher' => true]);

        file_put_contents($this->tempDir.'/foo.yaml', '');
        file_put_contents($this->tempDir.'/bar.yaml', '');
        $this->store->paths();

        unlink($this->tempDir.'/bar.yaml');
        file_put_contents($this->tempDir.'/baz.yaml', '');
        $store = $this->nextRequest();

        $this->assertNull($store->getItem('bar'));
        $this->assertNotNull($store->getItem('baz'));
        $this->assertEquals(['baz', 'foo'], $store->paths()->keys()->sort()->values()->all());
    }

    /**
     * A new store instance with nothing memoized, as the next request would get.
     * The cache (where the Stache keeps its data between requests) is untouched.
     */
    private function nextRequest()
    {
        $class = get_class($this->store);
        app('stache.indexes')->each->resetMemoizedState();
        app()->forgetInstance('stache.indexes');

        return $this->store = (new $class)->directory($this->tempDir);
    }
}

class TestBasicStore extends BasicStore
{
    public $madeItemCount = 0;

    public function key()
    {
        return 'test';
    }

    public function makeItemFromFile($path, $contents)
    {
        $this->madeItemCount++;

        $data = YAML::parse($contents);
        $id = Str::after($path, Path::tidy(__DIR__.'/tmp/'));
        $id = Str::before($id, '.yaml');

        return new TestBasicStoreItem($id, $data, $path);
    }
}

class TestBasicStoreWithIds extends TestBasicStore
{
    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);

        return new TestBasicStoreItem($data['id'], $data, $path);
    }
}

class TestBasicStoreItem
{
    private $id;
    private $data;
    private $path;

    public function __construct($id, $data, $path = null)
    {
        $this->id = $id;
        $this->data = $data;
        $this->path = $path;
    }

    public function id()
    {
        return $this->id;
    }

    public function data()
    {
        return $this->data;
    }

    public function path()
    {
        return $this->path;
    }
}
