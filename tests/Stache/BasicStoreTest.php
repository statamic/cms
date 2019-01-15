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
    function it_gets_and_sets_a_path_for_a_site()
    {
        $this->assertFalse($this->store->isUpdated());
        $this->assertNull($this->store->getSitePath('en', '123'));

        $return = $this->store->setSitePath('en', '123', '/one');

        $this->assertEquals('/one', $this->store->getSitePath('en', '123'));
        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    public function it_gets_and_sets_a_sites_paths()
    {
        $this->assertFalse($this->store->isUpdated());
        $this->assertEquals([], $this->store->getSitePaths('en')->all());
        $this->assertEquals([], $this->store->getSitePaths('fr')->all());

        $return = $this->store->setSitePaths('en', $enPaths = ['/one', '/two']);
        $return = $this->store->setSitePaths('fr', $frPaths = ['/un', '/deux']);

        $this->assertEquals($this->store, $return);
        $this->assertEquals($enPaths, $this->store->getSitePaths('en')->all());
        $this->assertEquals($frPaths, $this->store->getSitePaths('fr')->all());
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_gets_and_sets_paths_for_all_sites()
    {
        $this->assertFalse($this->store->isUpdated());
        $this->assertEquals([], $this->store->getSitePaths('en')->all());
        $this->assertEquals([], $this->store->getSitePaths('fr')->all());
        $this->assertEquals(['en' => collect(), 'fr' => collect()], $this->store->getPaths()->all());

        $return = $this->store->setPaths([
            'en' => $enPaths = ['one.md', 'two.md'],
            'fr' => $frPaths = ['un.md', 'deux.md'],
        ]);

        $this->assertEquals($this->store, $return);
        $this->assertEquals($enPaths, $this->store->getSitePaths('en')->all());
        $this->assertEquals($frPaths, $this->store->getSitePaths('fr')->all());
        $this->assertEquals(['en' => collect($enPaths), 'fr' => collect($frPaths)], $this->store->getPaths()->all());
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_gets_and_sets_a_uri_for_a_site()
    {
        $this->assertFalse($this->store->isUpdated());
        $this->assertNull($this->store->getSiteUri('en', '123'));

        $return = $this->store->setSiteUri('en', '123', '/one');

        $this->assertEquals('/one', $this->store->getSiteUri('en', '123'));
        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    public function it_gets_and_sets_a_sites_uris()
    {
        $this->assertFalse($this->store->isUpdated());
        $this->assertEquals([], $this->store->getSiteUris('en')->all());
        $this->assertEquals([], $this->store->getSiteUris('fr')->all());

        $return = $this->store->setSiteUris('en', $enUris = ['/one', '/two']);
        $return = $this->store->setSiteUris('fr', $frUris = ['/un', '/deux']);

        $this->assertEquals($this->store, $return);
        $this->assertEquals($enUris, $this->store->getSiteUris('en')->all());
        $this->assertEquals($frUris, $this->store->getSiteUris('fr')->all());
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_gets_and_sets_uris_for_all_sites()
    {
        $this->assertFalse($this->store->isUpdated());
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
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function inserting_an_item_will_set_the_item_and_path_and_uris()
    {
        $this->assertFalse($this->store->isUpdated());

        // Inserting an object with an id method should use that as the key
        $return = $this->store->insert($object = new class {
            public function id() { return '123'; }
            public function path() { return '/path/to/object'; }
            public function uri() { return '/the/uri'; }
        });

        $this->assertEquals($this->store, $return);
        $this->assertEquals(['123' => $object], $this->store->getItems()->all());
        $this->assertEquals([
            'en' => ['123' => '/path/to/object'],
            'fr' => []
        ], $this->store->getPaths()->toArray());
        $this->assertEquals([
            'en' => [
                '123' => '/the/uri',
            ],
            'fr' => [
                '123' => '/the/uri',
            ]
        ], $this->store->getUris()->toArray());
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function inserting_a_localizable_item_will_set_the_path_and_uri_for_each_site()
    {
        $entry = (new \Statamic\Data\Entries\Entry)->id('123');
        $en = new class extends \Statamic\Data\Entries\LocalizedEntry {
            public function path() { return '/path/to/en'; }
            public function uri() { return '/en'; }
        };
        $fr = new class extends \Statamic\Data\Entries\LocalizedEntry {
            public function path() { return '/path/to/fr'; }
            public function uri() { return '/fr'; }
        };
        $entry
            ->addLocalization($en->locale('en'))
            ->addLocalization($fr->locale('fr'));

        $this->store->insert($entry);

        $this->assertEquals([
            'en' => ['123' => '/path/to/en'],
            'fr' => ['123' => '/path/to/fr'],
        ], $this->store->getPaths()->toArray());
        $this->assertEquals([
            'en' => ['123' => '/en'],
            'fr' => ['123' => '/fr'],
        ], $this->store->getUris()->toArray());
    }

    /** @test */
    function items_can_be_removed_by_path()
    {
        $collection = (new \Statamic\Data\Entries\Collection)->handle('test');

        // an item that will remain at the end
        $firstItem = (new \Statamic\Data\Entries\Entry)->id('first')->collection($collection)
            ->addLocalization(new class extends \Statamic\Data\Entries\LocalizedEntry {
                public function locale($locale = null) { return 'en'; }
                public function path() { return '/path/to/first/item'; }
                public function uri() { return '/uri/of/first/item'; }
            });

        // an item with 1 localization that will be removed
        $secondItem = (new \Statamic\Data\Entries\Entry)->id('second')->collection($collection)
            ->addLocalization(new class extends \Statamic\Data\Entries\LocalizedEntry {
                public function locale($locale = null) { return 'en'; }
                public function path() { return '/path/to/second/item'; }
                public function uri() { return '/uri/of/second/item'; }
            });

        // an item with 2 localizations, one will be removed
        $thirdItem = (new \Statamic\Data\Entries\Entry)->id('third')->collection($collection)
            ->addLocalization(new class extends \Statamic\Data\Entries\LocalizedEntry {
                public function locale($locale = null) { return 'en'; }
                public function path() { return '/path/to/third/item'; }
                public function uri() { return '/uri/of/third/item'; }
            })
            ->addLocalization(new class extends \Statamic\Data\Entries\LocalizedEntry {
                public function locale($locale = null) { return 'fr'; }
                public function path() { return '/path/to/third/item/in/french'; }
                public function uri() { return '/uri/of/third/item/in/french'; }
            });

        // a non-localizable item that will be removed
        $fourthItem = new class {
            public function id() { return 'fourth'; }
            public function path() { return '/path/to/fourth/item'; }
            public function uri() { return '/uri/of/fourth/item'; }
        };

        $this->store->withoutMarkingAsUpdated(function () use ($firstItem, $secondItem, $thirdItem, $fourthItem) {
            $this->store->insert($firstItem);
            $this->store->insert($secondItem);
            $this->store->insert($thirdItem);
            $this->store->insert($fourthItem);
        });

        $this->assertFalse($this->store->isUpdated());
        $this->assertEquals(4, $this->store->getItemsWithoutLoading()->count());

        $return = $this->store->removeByPath('/path/to/second/item');
        $this->store->removeByPath('/path/to/third/item/in/french');
        $this->store->removeByPath('/path/to/fourth/item');

        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isUpdated());
        $this->assertEquals([
            'first' => $firstItem,
            'third' => $thirdItem,
        ], $this->store->getItems()->all());
        $this->assertEquals(1, $this->store->getItem('third')->localizations()->count());
    }

    /** @test */
    function removing_an_item_will_remove_the_item_and_paths_and_uris()
    {
        $firstItem = new class {
            public function id() { return '123'; }
            public function path() { return '/first/path'; }
            public function uri() { return '/first/uri'; }
        };
        $secondItem = new class {
            public function id() { return '456'; }
            public function path() { return '/second/path'; }
        };
        $fourthItem = new class {
            public function id() { return '131415'; }
            public function path() { return '/fourth/path'; }
            public function uri() { return '/fourth/uri'; }
        };
        $this->store->withoutMarkingAsUpdated(function () use ($firstItem, $secondItem, $fourthItem) {
            $this->store->insert($firstItem);
            $this->store->insert($secondItem);
            $this->store->insert($fourthItem);
        });
        $this->assertFalse($this->store->isUpdated());
        $this->assertEquals(3, $this->store->getItemsWithoutLoading()->count());
        $this->assertEquals([
            '123' => '/first/uri',
            '131415' => '/fourth/uri',
        ], $this->store->getSiteUris('en')->all());

        $return = $this->store->remove('123');
        $this->store->remove($secondItem);

        $this->assertEquals($this->store, $return);
        $this->assertEquals([
            '131415' => $fourthItem,
        ], $this->store->getItems()->all());
        $this->assertEquals([
            '131415' => '/fourth/path',
        ], $this->store->getSitePaths('en')->all());
        $this->assertEquals([
            'en' => ['131415' => '/fourth/uri'],
            'fr' => ['131415' => '/fourth/uri']
        ], $this->store->getUris()->toArray());
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_gets_an_id_from_a_uri()
    {
        $this->assertFalse($this->store->isUpdated());

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
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_gets_an_id_from_a_path()
    {
        $this->assertFalse($this->store->isUpdated());

        $this->store->setSitePaths('en', [
            '123' => '/one',
            '456' => '/two'
        ]);
        $this->store->setSitePaths('fr', [
            '123' => '/un',
            '789' => '/trois'
        ]);

        $this->assertEquals('123', $this->store->getIdFromPath('/one'));
        $this->assertEquals('456', $this->store->getIdFromPath('/two'));
        $this->assertEquals('123', $this->store->getIdFromPath('/un'));
        $this->assertEquals('789', $this->store->getIdFromPath('/trois'));
        $this->assertNull($this->store->getIdFromPath('/unknown'));
        $this->assertTrue($this->store->isUpdated());
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
        $this->assertFalse($this->store->isUpdated());
        $this->assertNull($this->store->getItem('123'));

        $return = $this->store->setItem('123', $item = ['title' => 'Item title']);

        $this->assertEquals($item, $this->store->getItem('123'));
        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_removes_an_item_by_key()
    {
        $this->assertFalse($this->store->isUpdated());
        $item = ['title' => 'Item one'];
        Cache::shouldReceive('get')->with('stache::items/test')->andReturn(['123' => $item]);
        $this->assertEquals($item, $this->store->getItem('123'));

        $return = $this->store->removeItem('123');

        $this->assertNull($this->store->getItem('123'));
        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isUpdated());
    }

    /** @test */
    function it_caches_items_and_meta_data()
    {
        $this->store->setSitePath('en', '1', '/path/to/one.txt');
        $this->store->setSiteUri('en', '1', '/one');
        $this->store->setItem('1', new class {
            public function toCacheableArray() {
                return 'converted using toCacheableArray';
            }
        });

        $this->store->setSitePath('en', '2', '/path/to/two.txt');
        $this->store->setSitePath('fr', '2', '/path/to/deux.txt');
        $this->store->setSiteUri('en', '2', '/two');
        $this->store->setSiteUri('fr', '2', '/deux');
        $this->store->setItem('2', ['item' => 'two']);
        Cache::shouldReceive('forever')->once()->with('stache::items/test', [
            '1' => 'converted using toCacheableArray',
            '2' => ['item' => 'two'],
        ]);
        Cache::shouldReceive('forever')->once()->with('stache::meta/test', [
            'paths' => [
                'en' => [
                    '1' => '/path/to/one.txt',
                    '2' => '/path/to/two.txt',
                ],
                'fr' => [
                    '2' => '/path/to/deux.txt',
                ],
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
        Cache::shouldReceive('get')->with('stache::meta/test', Mockery::any())->once()->andReturn('what was in the cache');

        $this->assertEquals(['test' => 'what was in the cache'], $this->store->getMetaFromCache());
    }

    /** @test */
    function it_gets_a_map_of_ids_to_the_stores()
    {
        $this->store->setSitePaths('en', ['123' => '/path/to/one.md', '456' => '/path/to/two.md']);
        $this->store->setSitePaths('fr', ['123' => '/path/to/deux.md', '789' => '/path/to/tres.md']);

        $this->assertEquals([
            '123' => 'test',
            '456' => 'test',
            '789' => 'test',
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
