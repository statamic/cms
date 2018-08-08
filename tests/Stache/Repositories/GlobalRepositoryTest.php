<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\GlobalsStore;
use Statamic\Data\Globals\GlobalCollection;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Statamic\Stache\Repositories\GlobalRepository;
use Illuminate\Support\Collection as IlluminateCollection;

class GlobalRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $stache->registerStore((new GlobalsStore($stache))->directory(__DIR__.'/../__fixtures__/content/globals'));

        $this->repo = new GlobalRepository($stache);
    }

    /** @test */
    function it_gets_all_global_sets()
    {
        $sets = $this->repo->all();

        $this->assertInstanceOf(GlobalCollection::class, $sets);
        $this->assertCount(2, $sets);
        $this->assertEveryItemIsInstanceOf(GlobalSet::class, $sets);

        $ordered = $sets->sortBy->path()->values();
        $this->assertEquals(['globals-contact', 'globals-global'], $ordered->map->id()->all());
        $this->assertEquals(['contact', 'global'], $ordered->map->slug()->all()); // @TODO: Change to handle()
        $this->assertEquals(['Contact Details', 'General'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_a_global_set_by_id()
    {
        tap($this->repo->find('globals-global'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-global', $set->id());
            $this->assertEquals('global', $set->slug()); // @TODO: Change to handle()
            $this->assertEquals('General', $set->title());
        });

        tap($this->repo->find('globals-contact'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-contact', $set->id());
            $this->assertEquals('contact', $set->slug()); // @TODO: Change to handle()
            $this->assertEquals('Contact Details', $set->title());
        });

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    function it_gets_a_global_set_by_handle()
    {
        tap($this->repo->findByHandle('global'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-global', $set->id());
            $this->assertEquals('global', $set->slug()); // @TODO: Change to handle()
            $this->assertEquals('General', $set->title());
        });

        tap($this->repo->findByHandle('contact'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-contact', $set->id());
            $this->assertEquals('contact', $set->slug()); // @TODO: Change to handle()
            $this->assertEquals('Contact Details', $set->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }
}
