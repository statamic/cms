<?php

namespace Tests\Stache;

use Illuminate\Filesystem\Filesystem;
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
}

class TestBasicStore extends BasicStore
{
    public function key()
    {
        return 'test';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);
        $id = Str::after($path, Path::tidy(__DIR__.'/tmp/'));
        $id = Str::before($id, '.yaml');

        return new TestBasicStoreItem($id, $data);
    }
}

class TestBasicStoreItem
{
    private $id;
    private $data;

    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    public function id()
    {
        return $this->id;
    }
}
