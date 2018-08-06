<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\ChildStore;

class AggregateStoreTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)
            ->sites(['en', 'fr'])
            ->keys(['test/data']);

        $this->store = new TestAggregateStore($stache);
    }

    /** @test */
    function it_gets_and_sets_child_stores()
    {
        $this->assertEquals([], $this->store->stores()->all());

        $childOne = $this->store->store('one');
        $childTwo = $this->store->store('two');

        $this->assertInstanceOf(ChildStore::class, $childOne);
        $this->assertEquals(['one' => $childOne, 'two' => $childTwo], $this->store->stores()->all());
    }

    /** @test */
    function it_sets_paths_in_all_child_stores()
    {
        $this->assertEquals([], $this->store->store('a')->getPaths()->all());
        $this->assertEquals([], $this->store->store('b')->getPaths()->all());

        $return = $this->store->setPaths($paths = [
            'a::one' => 'one.md',
            'b::two' => 'two.md'
        ]);

        $this->assertEquals($this->store, $return);
        $this->assertEquals(['one' => 'one.md'], $this->store->store('a')->getPaths()->all());
        $this->assertEquals(['two' => 'two.md'], $this->store->store('b')->getPaths()->all());
    }

    /** @test */
    function it_sets_specific_path_in_child_store()
    {
        $this->assertNull($this->store->store('a')->getPath('one'));

        $return = $this->store->setPath('a::one', 'one.md');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('one.md', $this->store->store('a')->getPath('one'));
    }

    /** @test */
    function it_is_loaded_if_all_child_stores_are_loaded()
    {
        $this->store->store('one');
        $this->store->store('two');
        $this->assertFalse($this->store->isLoaded());

        $this->store->store('one')->load();
        $this->assertFalse($this->store->isLoaded());

        $this->store->store('two')->load();
        $this->assertTrue($this->store->isLoaded());
    }

    /** @test */
    function it_loads_all_child_stores()
    {
        $this->store->store('one');
        $this->store->store('two');
        $this->assertFalse($this->store->isLoaded());
        $this->assertFalse($this->store->store('one')->isLoaded());
        $this->assertFalse($this->store->store('two')->isLoaded());

        $this->store->load();

        $this->assertTrue($this->store->isLoaded());
        $this->assertTrue($this->store->store('one')->isLoaded());
        $this->assertTrue($this->store->store('two')->isLoaded());
    }

    /** @test */
    function it_marks_all_child_stores_as_loaded()
    {
        $this->store->store('one');
        $this->store->store('two');
        $this->assertFalse($this->store->isLoaded());
        $this->assertFalse($this->store->store('one')->isLoaded());
        $this->assertFalse($this->store->store('two')->isLoaded());

        $return = $this->store->markAsLoaded();

        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isLoaded());
        $this->assertTrue($this->store->store('one')->isLoaded());
        $this->assertTrue($this->store->store('two')->isLoaded());
    }

    /** @test */
    function it_gets_all_items()
    {
        Cache::shouldReceive('get')->with('stache::test::one/data')->andReturn($items = [
            '123' => ['title' => 'Store One Item One'],
            '456' => ['title' => 'Store One Item Two'],
        ]);
        Cache::shouldReceive('get')->with('stache::test::two/data')->andReturn($items = [
            '789' => ['title' => 'Store Two Item One'],
            '101' => ['title' => 'Store Two Item Two'],
        ]);

        $this->store->store('one')
            ->setItem('123', ['title' => 'Store One Item One'])
            ->setItem('456', ['title' => 'Store One Item Two']);

        $this->store->store('two')
            ->setItem('789', ['title' => 'Store Two Item One'])
            ->setItem('101', ['title' => 'Store Two Item Two']);

        $this->assertEquals([
            'one' => [
                '123' => ['title' => 'Store One Item One'],
                '456' => ['title' => 'Store One Item Two'],
            ],
            'two' => [
                '789' => ['title' => 'Store Two Item One'],
                '101' => ['title' => 'Store Two Item Two'],
            ]
        ], $this->store->getItems()->toArray());
    }

    /** @test */
    function it_sets_child_items()
    {
        $this->assertEquals([], $this->store->store('one')->getItemsWithoutLoading()->all());

        $this->store->setItem('one::123', ['title' => 'Store One Item One']);
        $this->store->setItem('one::456', ['title' => 'Store One Item Two']);
        $this->store->setItem('two::789', ['title' => 'Store Two Item One']);
        $return = $this->store->setItem('two::101', ['title' => 'Store Two Item Two']);

        $this->assertEquals([
            'one' => [
                '123' => ['title' => 'Store One Item One'],
                '456' => ['title' => 'Store One Item Two'],
            ],
            'two' => [
                '789' => ['title' => 'Store Two Item One'],
                '101' => ['title' => 'Store Two Item Two'],
            ]
        ], $this->store->getItemsWithoutLoading()->toArray());

        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_gets_and_sets_a_uri_for_a_child_stores_site()
    {
        $this->assertNull($this->store->getSiteUri('en', 'one::123'));

        $return = $this->store->setSiteUri('en', 'one::123', '/one');

        $this->assertEquals('/one', $this->store->getSiteUri('en', 'one::123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_can_perform_an_action_for_each_child_stores_site()
    {
        $arguments = [];
        $this->assertNull($this->store->store('one')->getSiteUri('en', '123'));
        $this->assertNull($this->store->store('one')->getSiteUri('fr', '123'));

        $return = $this->store->forEachSite(function ($site, $store) use (&$arguments) {
            $arguments[] = [$site, $store];
            $store->setSiteUri($site, 'one::123', '/url-in-' . $site);
        });

        $this->assertEquals([['en', $this->store], ['fr', $this->store]], $arguments);
        $this->assertEquals('/url-in-en', $this->store->getSiteUri('en', 'one::123'));
        $this->assertEquals('/url-in-fr', $this->store->getSiteUri('fr', 'one::123'));
        $this->assertEquals($this->store, $return);
    }
}

class TestAggregateStore extends AggregateStore
{
    public function key()
    {
        return 'test';
    }

    public function getItemsFromCache($cache)
    {
        return $cache;
    }
}
