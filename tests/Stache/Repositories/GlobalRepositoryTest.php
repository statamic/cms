<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\GlobalsStore;
use Statamic\API\GlobalSet as GlobalSetAPI;
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
        $this->directory = __DIR__.'/../__fixtures__/content/globals';
        $stache->registerStore((new GlobalsStore($stache, app('files')))->directory($this->directory));

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
        $this->assertEquals(['contact', 'global'], $ordered->map->slug()->all()); // TODO: Change to handle()
        $this->assertEquals(['Contact Details', 'General'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_a_global_set_by_id()
    {
        tap($this->repo->find('globals-global'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-global', $set->id());
            $this->assertEquals('global', $set->slug()); // TODO: Change to handle()
            $this->assertEquals('General', $set->title());
        });

        tap($this->repo->find('globals-contact'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-contact', $set->id());
            $this->assertEquals('contact', $set->slug()); // TODO: Change to handle()
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
            $this->assertEquals('global', $set->slug()); // TODO: Change to handle()
            $this->assertEquals('General', $set->title());
        });

        tap($this->repo->findByHandle('contact'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('globals-contact', $set->id());
            $this->assertEquals('contact', $set->slug()); // TODO: Change to handle()
            $this->assertEquals('Contact Details', $set->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    function it_saves_a_global_to_the_stache_and_to_a_file()
    {
        $global = GlobalSetAPI::create('new')
            ->with(['id' => 'id-new', 'foo' => 'bar', 'baz' => 'qux'])
            ->get();
        $global->in('fr')->set('foo', 'le bar');
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($global);

        $this->assertNotNull($item = $this->repo->find('id-new'));
        $this->assertEquals(['id' => 'id-new', 'foo' => 'bar', 'baz' => 'qux'], $item->in('en')->data());
        $this->assertEquals(['foo' => 'le bar'], $item->in('fr')->data());
        $this->assertFileExists($this->directory.'/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }
}
