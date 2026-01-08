<?php

namespace Tests\Stache;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\ChildStore;
use Tests\TestCase;

class AggregateStoreTest extends TestCase
{
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);

        $this->app->instance(Stache::class, $stache);

        $this->store = new TestAggregateStore;
    }

    #[Test]
    public function it_gets_and_sets_child_stores()
    {
        $this->assertEquals([], $this->store->stores()->all());

        $childOne = $this->store->store('one');
        $childTwo = $this->store->store('two');

        $this->assertInstanceOf(ChildStore::class, $childOne);
        $this->assertEquals(['one' => $childOne, 'two' => $childTwo], $this->store->stores()->all());
    }

    #[Test]
    public function it_gets_items_from_multiple_child_stores()
    {
        $this->store->store('one')->setItems([
            'a' => ['id' => 'a', 'title' => 'Item A'],
            'b' => ['id' => 'b', 'title' => 'Item B'],
        ]);

        $this->store->store('two')->setItems([
            'c' => ['id' => 'c', 'title' => 'Item C'],
            'd' => ['id' => 'd', 'title' => 'Item D'],
        ]);

        $items = $this->store->getItems(['one::a', 'two::c', 'one::b']);

        $this->assertCount(3, $items);
        $this->assertEquals('a', $items[0]['id']);
        $this->assertEquals('c', $items[1]['id']);
        $this->assertEquals('b', $items[2]['id']);
    }

    #[Test]
    public function it_gets_items_preserving_order_across_stores()
    {
        $this->store->store('one')->setItems([
            'a' => ['id' => 'a'],
            'b' => ['id' => 'b'],
        ]);

        $this->store->store('two')->setItems([
            'c' => ['id' => 'c'],
            'd' => ['id' => 'd'],
        ]);

        // Request in mixed order
        $items = $this->store->getItems(['two::d', 'one::a', 'two::c', 'one::b']);

        $this->assertEquals(['d', 'a', 'c', 'b'], $items->pluck('id')->all());
    }

    #[Test]
    public function it_returns_empty_collection_for_empty_keys()
    {
        $items = $this->store->getItems([]);

        $this->assertCount(0, $items);
    }
}

class TestAggregateStore extends AggregateStore
{
    protected $childStore = TestChildStore::class;

    public function key()
    {
        return 'test';
    }

    public function discoverStores()
    {
        //
    }
}

class TestChildStore extends ChildStore
{
    protected $items = [];

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getItem($key)
    {
        return $this->items[$key] ?? null;
    }

    public function getItems($keys)
    {
        return collect($keys)->map(fn ($key) => $this->getItem($key));
    }
}
