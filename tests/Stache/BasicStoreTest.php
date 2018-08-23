<?php

namespace Tests\Stache;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;

class BasicStoreTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->stache = (new Stache)
            ->sites(['en', 'fr'])
            ->keys(['test/data']);

        $this->store = new TestBasicStore($this->stache, app('files'));
    }

    /** @test */
    function it_gets_and_sets_paths()
    {
        $this->assertEquals([], $this->store->getPaths()->all());

        $return = $this->store->setPaths($paths = ['one.md', 'two.md']);

        $this->assertEquals($this->store, $return);
        $this->assertEquals($paths, $this->store->getPaths()->all());
    }

    /** @test */
    function it_gets_and_sets_single_paths()
    {
        $this->assertNull($this->store->getPath('one'));

        $return = $this->store->setPath('one', 'one.md');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('one.md', $this->store->getPath('one'));
    }

    /** @test */
    function it_gets_and_sets_a_uri_for_a_site()
    {
        $this->assertNull($this->store->getSiteUri('en', '123'));

        $return = $this->store->setSiteUri('en', '123', '/one');

        $this->assertEquals('/one', $this->store->getSiteUri('en', '123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    public function it_gets_and_sets_a_sites_uris()
    {
        $this->assertEquals([], $this->store->getSiteUris('en')->all());
        $this->assertEquals([], $this->store->getSiteUris('fr')->all());

        $return = $this->store->setSiteUris('en', $enUris = ['/one', '/two']);
        $return = $this->store->setSiteUris('fr', $frUris = ['/un', '/deux']);

        $this->assertEquals($this->store, $return);
        $this->assertEquals($enUris, $this->store->getSiteUris('en')->all());
        $this->assertEquals($frUris, $this->store->getSiteUris('fr')->all());
    }

    /** @test */
    function it_gets_and_sets_uris_for_all_sites()
    {
        $this->assertEquals([], $this->store->getSiteUris('en')->all());
        $this->assertEquals([], $this->store->getSiteUris('fr')->all());
        $this->assertEquals(['en' => collect(), 'fr' => collect()], $this->store->getUris()->all());

        $return = $this->store->setUris([
            'en' => $enUris = ['one.md', 'two.md'],
            'fr' => $frUris = ['un.md', 'deux.md'],
        ]);

        $this->assertEquals($this->store, $return);
        $this->assertEquals($enUris, $this->store->getSiteUris('en')->all());
        $this->assertEquals($frUris, $this->store->getSiteUris('fr')->all());
        $this->assertEquals(['en' => collect($enUris), 'fr' => collect($frUris)], $this->store->getUris()->all());
    }

    /** @test */
    function inserting_an_item_will_set_the_item_and_path_and_uris()
    {
        // Inserting an object with an id method should use that as the key
        $return = $this->store->insert($object = new class {
            public function id() { return '123'; }
            public function path() { return '/path/to/object'; }
            public function uri() { return '/the/uri'; }
        });

        // Inserting an item with the key and path parameters will use those
        $this->store->insert(['title' => 'Item title'], '456', '/the/path');

        $this->assertEquals($this->store, $return);
        $this->assertEquals([
            '123' => $object,
            '456' => ['title' => 'Item title'],
        ], $this->store->getItems()->all());
        $this->assertEquals([
            '123' => '/path/to/object',
            '456' => '/the/path',
        ], $this->store->getPaths()->all());
        $this->assertEquals([
            'en' => [
                '123' => '/the/uri',
            ],
            'fr' => [
                '123' => '/the/uri',
            ]
        ], $this->store->getUris()->toArray());
    }

    /** @test */
    function it_gets_an_id_from_a_uri()
    {
        $this->store->setUris([
            'en' => $enUris = ['123' => '/one', '456' => '/two'],
            'fr' => $frUris = ['123' => '/un', '456' => '/deux'],
        ]);

        $this->assertEquals('123', $this->store->getIdFromUri('/one'));
        $this->assertEquals('123', $this->store->getIdFromUri('/one', 'en'));
        $this->assertEquals('123', $this->store->getIdFromUri('/un', 'fr'));
        $this->assertEquals('456', $this->store->getIdFromUri('/two'));
        $this->assertEquals('456', $this->store->getIdFromUri('/two', 'en'));
        $this->assertEquals('456', $this->store->getIdFromUri('/deux', 'fr'));
        $this->assertNull($this->store->getIdFromUri('/unknown'));
        $this->assertNull($this->store->getIdFromUri('/unknown', 'en'));
        $this->assertNull($this->store->getIdFromUri('/unknown', 'fr'));
    }

    /** @test */
    function it_can_perform_an_action_for_each_site()
    {
        $arguments = [];
        $this->assertNull($this->store->getSiteUri('en', '123'));
        $this->assertNull($this->store->getSiteUri('fr', '123'));

        $return = $this->store->forEachSite(function ($site, $store) use (&$arguments) {
            $arguments[] = [$site, $store];
            $store->setSiteUri($site, '123', '/url-in-' . $site);
        });

        $this->assertEquals([['en', $this->store], ['fr', $this->store]], $arguments);
        $this->assertEquals('/url-in-en', $this->store->getSiteUri('en', '123'));
        $this->assertEquals('/url-in-fr', $this->store->getSiteUri('fr', '123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_can_be_marked_as_loaded()
    {
        $this->assertFalse($this->store->isLoaded());

        $return = $this->store->markAsLoaded();

        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isLoaded());
    }

    /** @test */
    function items_can_be_loaded()
    {
        $this->store = new class($this->stache, app('files')) extends TestBasicStore {
            public $timesSetItemWasCalled = 0;
            public function setItem($key, $value) {
                parent::setItem($key, $value);
                $this->timesSetItemWasCalled++;
            }
        };

        Cache::shouldReceive('get')->with('stache::items/test')->andReturn($items = [
            // These items are irrelevant here. The test for the content
            // of these are driven out in the individual store tests.
            '123' => ['title' => 'Item one'],
            '456' => ['title' => 'Item two'],
        ]);

        $this->assertFalse($this->store->isLoaded());
        $this->assertEquals(0, $this->store->getItemsWithoutLoading()->count());
        $this->assertEquals(0, $this->store->timesSetItemWasCalled);

        $return = $this->store->load();

        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isLoaded());
        $this->assertEquals($items, $this->store->getItemsWithoutLoading()->all());

        // Some stores are relying on setItem to be called for every item. For example, the StructuresStore
        // updates entry URIs whenever a Structure is set into the store. If we just do something like
        // `$this->items = $items` then the StructuresStore (or maybe something else) could break.
        $this->assertEquals(2, $this->store->timesSetItemWasCalled);
    }

    /** @test */
    function items_are_an_empty_collection_if_theres_nothing_in_the_cache()
    {
        Cache::shouldReceive('get')->with('stache::items/test')->andReturnNull();

        $return = $this->store->load();

        $this->assertTrue($this->store->isLoaded());
        $this->assertEquals([], $this->store->getItemsWithoutLoading()->all());
    }

    /** @test */
    function items_dont_get_reloaded_if_they_have_already_been_loaded()
    {
        Cache::spy();

        $return = $this->store->markAsLoaded()->load();

        Cache::shouldNotHaveReceived('get');
        $this->assertTrue($this->store->isLoaded()); // Need an assertion. The spy isn't counted.
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function items_can_be_retrieved_without_loading()
    {
        Cache::spy();

        $items = $this->store->getItemsWithoutLoading();

        Cache::shouldNotHaveReceived('get');
        $this->assertEquals([], $items->all());
        $this->assertFalse($this->store->isLoaded());
    }

    /** @test */
    function items_are_loaded_on_demand()
    {
        Cache::shouldReceive('get')->with('stache::items/test')->andReturn([
            // These items are irrelevant here. The test for the content
            // of these are driven out in the individual store tests.
            '123' => ['title' => 'Item one'],
            '456' => ['title' => 'Item two'],
        ]);

        $this->assertFalse($this->store->isLoaded());
        $this->assertEquals(0, $this->store->getItemsWithoutLoading()->count());

        $items = $this->store->getItems();

        $this->assertEquals(2, $items->count());
        $this->assertEquals(2, $this->store->getItemsWithoutLoading()->count());
        $this->assertTrue($this->store->isLoaded());
    }

    /** @test */
    function it_gets_an_item_by_key()
    {
        $one = ['title' => 'Item one'];
        $two = ['title' => 'Item two'];
        Cache::shouldReceive('get')->with('stache::items/test')->andReturn(['123' => $one, '456' => $two]);

        $this->assertEquals($one, $this->store->getItem('123'));
        $this->assertEquals($two, $this->store->getItem('456'));
    }

    /** @test */
    function it_sets_an_item_by_key()
    {
        $this->assertNull($this->store->getItem('123'));

        $return = $this->store->setItem('123', $item = ['title' => 'Item title']);

        $this->assertEquals($item, $this->store->getItem('123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_removes_an_item_by_key()
    {
        $item = ['title' => 'Item one'];
        Cache::shouldReceive('get')->with('stache::items/test')->andReturn(['123' => $item]);
        $this->assertEquals($item, $this->store->getItem('123'));

        $return = $this->store->removeItem('123');

        $this->assertNull($this->store->getItem('123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_caches_items_and_meta_data()
    {
        $this->store->setPath('1', '/path/to/one.txt');
        $this->store->setSiteUri('en', '1', '/one');
        $this->store->setItem('1', new class {
            public function toCacheableArray() {
                return 'converted using toCacheableArray';
            }
        });

        $this->store->setPath('2', '/path/to/two.txt');
        $this->store->setSiteUri('en', '2', '/two');
        $this->store->setSiteUri('fr', '2', '/deux');
        $this->store->setItem('2', ['item' => 'two']);
        Cache::shouldReceive('forever')->once()->with('stache::items/test', [
            '1' => 'converted using toCacheableArray',
            '2' => ['item' => 'two'],
        ]);
        Cache::shouldReceive('forever')->once()->with('stache::meta/test', [
            'paths' => [
                '1' => '/path/to/one.txt',
                '2' => '/path/to/two.txt',
            ],
            'uris' => [
                'en' => [
                    '1' => '/one',
                    '2' => '/two'
                ],
                'fr' => [
                    '2' => '/deux'
                ]
            ]
        ]);

        $this->store->cache();
    }

    /** @test */
    function gets_meta_data_from_cache_in_a_format_suitable_for_collection_mapWithKeys_method()
    {
        Cache::shouldReceive('get')->with('stache::meta/test')->once()->andReturn('what was in the cache');

        $this->assertEquals(['test' => 'what was in the cache'], $this->store->getMetaFromCache());
    }

    /** @test */
    function it_gets_a_map_of_ids_to_the_stores()
    {
        $this->store->setPaths(['123' => '/path/to/one.md', '456' => '/path/to/two.md']);

        $this->assertEquals([
            '123' => 'test',
            '456' => 'test',
        ], $this->store->getIdMap()->all());
    }
}

class TestBasicStore extends BasicStore
{
    public function key()
    {
        return 'test';
    }
}
