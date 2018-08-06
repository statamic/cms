<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Fakes\YAML;
use Illuminate\Support\Facades\Cache;
use Facades\Statamic\Stache\API\Entry;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\CollectionsStore;

class FeatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->stache = (new Stache)->sites(['en', 'es']);
        $this->app->instance(Stache::class, $this->stache);
    }

    /** @test */
    function it_gets_all_collections()
    {
        $this->stache->registerStore(
            (new CollectionsStore($this->stache))
                ->directory(__DIR__.'/__fixtures__/content/collections/')
        )->boot();

        $this->assertEquals(4, $this->allCollections()->count());
    }

    /** @test */
    function it_gets_all_entries()
    {
        $this->stache->registerStore(
            (new EntriesStore($this->stache))
                ->directory(__DIR__.'/__fixtures__/content/collections/')
        )->boot();

        $this->assertEquals(14, $this->allEntries()->count());
        $this->assertEquals(3, $this->entryWhereCollection('alphabetical')->count());
        $this->assertEquals(2, $this->entryWhereCollection('blog')->count());
        $this->assertEquals(3, $this->entryWhereCollection('numeric')->count());
        $this->assertEquals(6, $this->entryWhereCollection('pages')->count());
    }

    private function allCollections()
    {
        // A fake implementation of Statamic\API\Collection::all();
        return $this->stache->store('collections')->getItems();
    }

    private function allEntries()
    {
        // A fake implementation of Statamic\API\Entry::all();
        return $this->stache->store('entries')->getItemsWithoutLoading()->flatMap->all();
    }

    protected function entryWhereCollection($collection)
    {
        // A fake implementation of Statamic\API\Entry::whereCollection();
        return $this->stache->store('entries')->store($collection)->getItemsWithoutLoading();
    }
}
