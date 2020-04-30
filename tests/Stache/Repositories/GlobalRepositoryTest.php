<?php

namespace Tests\Stache\Repositories;

use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Facades\GlobalSet as GlobalSetAPI;
use Statamic\Globals\GlobalCollection;
use Statamic\Stache\Repositories\GlobalRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\GlobalsStore;
use Tests\TestCase;

class GlobalRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/globals';
        $stache->registerStore((new GlobalsStore($stache, app('files')))->directory($this->directory));

        $this->repo = new GlobalRepository($stache);
    }

    /** @test */
    public function it_gets_all_global_sets()
    {
        $sets = $this->repo->all();

        $this->assertInstanceOf(GlobalCollection::class, $sets);
        $this->assertCount(2, $sets);
        $this->assertEveryItemIsInstanceOf(GlobalSet::class, $sets);

        $ordered = $sets->sortBy->path()->values();
        $this->assertEquals(['contact', 'global'], $ordered->map->id()->all());
        $this->assertEquals(['contact', 'global'], $ordered->map->handle()->all());
        $this->assertEquals(['Contact Details', 'General'], $ordered->map->title()->all());
    }

    /** @test */
    public function it_gets_a_global_set_by_id()
    {
        tap($this->repo->find('global'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('global', $set->id());
            $this->assertEquals('global', $set->handle());
            $this->assertEquals('General', $set->title());
        });

        tap($this->repo->find('contact'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('contact', $set->id());
            $this->assertEquals('contact', $set->handle());
            $this->assertEquals('Contact Details', $set->title());
        });

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    public function it_gets_a_global_set_by_handle()
    {
        tap($this->repo->findByHandle('global'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('global', $set->id());
            $this->assertEquals('global', $set->handle());
            $this->assertEquals('General', $set->title());
        });

        tap($this->repo->findByHandle('contact'), function ($set) {
            $this->assertInstanceOf(GlobalSet::class, $set);
            $this->assertEquals('contact', $set->id());
            $this->assertEquals('contact', $set->handle());
            $this->assertEquals('Contact Details', $set->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    public function it_saves_a_global_to_the_stache_and_to_a_file()
    {
        $global = GlobalSetAPI::make('new');

        $global->addLocalization(
            $global->makeLocalization('en')->data(['foo' => 'bar', 'baz' => 'qux'])
        );

        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($global);

        $this->assertNotNull($item = $this->repo->find('new'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $item->in('en')->data()->all());
        $this->assertFileExists($this->directory.'/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }
}
