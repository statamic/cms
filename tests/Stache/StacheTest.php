<?php

namespace Tests\Stache;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Stache as StacheFacade;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Stache\NullLockStore;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;
use Symfony\Component\Lock\LockFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StacheTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $stache;

    public function setUp(): void
    {
        parent::setUp();
        $this->stache = new Stache;
    }

    #[Test]
    public function sites_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->sites());

        $return = $this->stache->sites(['one', 'two']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->sites());
        $this->assertEquals(['one', 'two'], $this->stache->sites()->all());
    }

    #[Test]
    public function default_site_can_be_retrieved()
    {
        $this->stache->sites(['foo', 'bar']);

        $this->assertEquals('foo', $this->stache->defaultSite());
    }

    #[Test]
    public function stores_can_be_registered()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $return = $this->stache->registerStore(
            new CollectionsStore($this->stache, \Mockery::mock(Filesystem::class))
        );

        $this->assertEquals($this->stache, $return);
        tap($this->stache->stores(), function ($stores) {
            $this->assertEquals(1, $stores->count());
            $this->assertEquals('collections', $stores->keys()->first());
            $this->assertInstanceOf(CollectionsStore::class, $stores->first());
            $this->assertInstanceOf(CollectionsStore::class, $this->stache->store('collections'));
        });
    }

    #[Test]
    public function stores_can_be_excluded_from_warming_and_clearing()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $mockStore = $this->mock(CollectionsStore::class, function ($mock) {
            $mock->shouldReceive('warm')->never();
            $mock->shouldReceive('clear')->never();
            $mock->shouldReceive('key')->andReturn('collections');
        });

        $this->stache->registerStore($mockStore);

        $return = $this->stache->exclude('collections');

        $this->assertEquals($this->stache, $return);

        $this->stache->setLockFactory(new LockFactory(new NullLockStore()));
        $this->stache->warm();
        $this->stache->clear();
    }

    #[Test]
    public function multiple_stores_can_be_registered_at_once()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $return = $this->stache->registerStores([
            new CollectionsStore($this->stache, \Mockery::mock(Filesystem::class)),
            new EntriesStore($this->stache, \Mockery::mock(Filesystem::class)),
        ]);

        $this->assertEquals($this->stache, $return);
        tap($this->stache->stores(), function ($stores) {
            $this->assertEquals(2, $stores->count());
            $this->assertEquals(['collections', 'entries'], $stores->keys()->all());
            $this->assertInstanceOf(CollectionsStore::class, $stores['collections']);
            $this->assertInstanceOf(EntriesStore::class, $stores['entries']);
            $this->assertInstanceOf(CollectionsStore::class, $this->stache->store('collections'));
            $this->assertInstanceOf(EntriesStore::class, $this->stache->store('entries'));
        });
    }

    #[Test]
    public function an_aggregate_stores_child_store_can_be_retrieved_directly()
    {
        $this->stache->sites(['en']); // stores expect the stache to have site(s)
        $store = (new EntriesStore($this->stache, \Mockery::mock(Filesystem::class)))->setChildStoreCreator(function () {
            return new ChildStore($this->stache, \Mockery::mock(Filesystem::class));
        });
        $one = $store->store('one');
        $two = $store->store('two');
        $this->stache->registerStore($store);

        $this->assertEquals($one, $this->stache->store('entries::one'));
        $this->assertEquals($two, $this->stache->store('entries::two'));
    }

    #[Test]
    public function it_generates_an_id()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_clears_its_cache()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function clearing_forgets_the_cached_collection_structure_trees()
    {
        Blink::put('collection-structure-tree-pages-en', 'cached');
        Blink::put('collection-structure-tree-entries::pages::en', 'cached');
        Blink::put('structure-pages-en-d751713988987e9331980363e24189ce', 'cached');
        Blink::put('unrelated-cache', 'kept');

        $this->stache->clear();

        $this->assertFalse(Blink::has('collection-structure-tree-pages-en'));
        $this->assertFalse(Blink::has('collection-structure-tree-entries::pages::en'));
        $this->assertFalse(Blink::has('structure-pages-en-d751713988987e9331980363e24189ce'));
        $this->assertTrue(Blink::has('unrelated-cache'));
    }

    #[Test]
    public function it_refreshes_itself()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_gets_its_cache_file_size()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_can_record_its_build_time()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[DataProvider('watcherProvider')]
    public function it_can_determine_if_watcher_is_enabled($environment, $config, $expected)
    {
        app()['env'] = $environment;

        config(['statamic.stache.watcher' => $config]);

        $this->assertEquals($expected, $this->stache->isWatcherEnabled());
    }

    public static function watcherProvider()
    {
        return [
            ['local', 'config' => true, 'expected' => true],
            ['production', 'config' => true, 'expected' => true],
            ['local', 'config' => false, 'expected' => false],
            ['production', 'config' => false, 'expected' => false],
            ['local', 'config' => 'auto', 'expected' => true],
            ['production', 'config' => 'auto', 'expected' => false],
            ['other', 'config' => 'auto', 'expected' => false],
            ['local', 'config' => null, 'expected' => false],
            ['production', 'config' => null, 'expected' => false],
        ];
    }

    #[Test]
    public function warming_builds_term_associations_for_single_item_taxonomy_fields()
    {
        Taxonomy::make('tags')->save();
        CollectionFacade::make('blog')->taxonomies(['tags'])->save();
        Blueprint::make('blog')->setNamespace('collections.blog')->setContents(['fields' => [
            ['handle' => 'tags', 'field' => ['type' => 'terms', 'taxonomies' => ['tags'], 'max_items' => 1]],
        ]])->save();
        Term::make('alfa')->taxonomy('tags')->data(['title' => 'Alfa'])->save();
        EntryFactory::collection('blog')->id('1')->slug('one')->data(['tags' => 'alfa'])->create();
        EntryFactory::collection('blog')->id('2')->slug('two')->data(['tags' => 'bravo'])->create();
        EntryFactory::collection('blog')->id('3')->slug('three')->data([])->create();

        StacheFacade::clear();
        StacheFacade::warm();

        $this->assertEquals([
            ['value' => 'alfa', 'slug' => 'alfa', 'entry' => '1', 'collection' => 'blog', 'site' => 'en'],
            ['value' => 'bravo', 'slug' => 'bravo', 'entry' => '2', 'collection' => 'blog', 'site' => 'en'],
        ], StacheFacade::store('terms')->store('tags')->index('associations')->items()->all());
    }
}
