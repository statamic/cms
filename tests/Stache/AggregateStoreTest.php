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
}
