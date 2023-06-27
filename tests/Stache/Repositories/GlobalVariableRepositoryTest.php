<?php

namespace Tests\Stache\Repositories;

use Statamic\Contracts\Globals\Variables;
use Statamic\Facades\GlobalSet as GlobalSetAPI;
use Statamic\Globals\VariableCollection;
use Statamic\Stache\Repositories\GlobalRepository;
use Statamic\Stache\Repositories\GlobalVariableRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\GlobalsStore;
use Statamic\Stache\Stores\GlobalVariablesStore;
use Tests\TestCase;

class GlobalVariableRepositoryTest extends TestCase
{
    private $directory;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/globals';
        $stache->registerStore((new GlobalsStore($stache, app('files')))->directory($this->directory));
        $stache->registerStore((new GlobalVariablesStore($stache, app('files')))->directory($this->directory));

        $this->repo = new GlobalVariableRepository($stache);
        $this->globalRepo = new GlobalRepository($stache);
    }

    /** @test */
    public function it_gets_all_global_variables()
    {
        $sets = $this->repo->all();

        $this->assertInstanceOf(VariableCollection::class, $sets);
        $this->assertCount(2, $sets);
        $this->assertEveryItemIsInstanceOf(Variables::class, $sets);

        $ordered = $sets->sortBy->path()->values();
        $this->assertEquals(['contact::en', 'global::en'], $ordered->map->id()->all());
        $this->assertEquals(['contact', 'global'], $ordered->map->handle()->all());
    }

    /** @test */
    public function it_gets_a_global_variable_by_id()
    {
        tap($this->repo->find('global::en'), function ($variable) {
            $this->assertInstanceOf(Variables::class, $variable);
            $this->assertEquals('global::en', $variable->id());
            $this->assertEquals('global', $variable->handle());
        });

        tap($this->repo->find('contact::en'), function ($variable) {
            $this->assertInstanceOf(Variables::class, $variable);
            $this->assertEquals('contact::en', $variable->id());
            $this->assertEquals('contact', $variable->handle());
        });

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    public function it_gets_global_variables_by_set_handle()
    {
        tap($this->repo->findBySet('global'), function ($variables) {
            $this->assertInstanceOf(VariableCollection::class, $variables);
            $first = $variables->first();
            $this->assertEquals('global::en', $first->id());
            $this->assertEquals('global', $first->handle());
        });

        tap($this->repo->findBySet('contact'), function ($variables) {
            $this->assertInstanceOf(VariableCollection::class, $variables);
            $first = $variables->first();
            $this->assertEquals('contact::en', $first->id());
            $this->assertEquals('contact', $first->handle());
        });

        $this->assertCount(0, $this->repo->findBySet('unknown'));
    }

    /** @test */
    public function it_saves_a_global_to_the_stache_and_to_a_file()
    {
        $global = GlobalSetAPI::make('new');

        $localization = $global->makeLocalization('en')->data(['foo' => 'bar', 'baz' => 'qux']);
        $global->addLocalization($localization);

        $this->assertNull($this->repo->find('new::en'));

        $this->globalRepo->save($global);
        $this->repo->save($localization);

        $this->assertNotNull($item = $this->repo->find('new::en'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $item->data()->all());
        $this->assertFileExists($this->directory.'/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }
}
