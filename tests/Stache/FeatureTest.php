<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\API\Entry;
use Statamic\Stache\Stache;
use Statamic\API\Collection;
use Statamic\Stache\Fakes\YAML;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\CollectionsStore;

class FeatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->stache = $this->app->make('stache');
        $this->stache->store('collections')->directory(__DIR__.'/__fixtures__/content/collections');
        $this->stache->store('entries')->directory(__DIR__.'/__fixtures__/content/collections');
        $this->stache->boot();
    }

    /** @test */
    function it_gets_all_collections()
    {
        $this->assertEquals(4, Collection::all()->count());
    }

    /** @test */
    function it_gets_all_entries()
    {
        $this->assertEquals(14, Entry::all()->count());
        $this->assertEquals(3, Entry::whereCollection('alphabetical')->count());
        $this->assertEquals(2, Entry::whereCollection('blog')->count());
        $this->assertEquals(3, Entry::whereCollection('numeric')->count());
        $this->assertEquals(6, Entry::whereCollection('pages')->count());
    }
}
