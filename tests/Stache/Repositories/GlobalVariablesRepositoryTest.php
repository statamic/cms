<?php

namespace Tests\Stache\Repositories;

use Statamic\Contracts\Globals\Variables;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Globals\VariablesCollection;
use Statamic\Stache\Repositories\GlobalRepository;
use Statamic\Stache\Repositories\GlobalVariablesRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\GlobalsStore;
use Statamic\Stache\Stores\GlobalVariablesStore;
use Tests\TestCase;

class GlobalVariablesRepositoryTest extends TestCase
{
    private $directory;
    private $repo;

    private function setUpSingleSite()
    {
        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/globals';
        $stache->registerStore((new GlobalsStore($stache, app('files')))->directory($this->directory));
        $stache->registerStore((new GlobalVariablesStore($stache, app('files')))->directory($this->directory));

        $this->repo = new GlobalVariablesRepository($stache);
        $this->globalRepo = new GlobalRepository($stache);
    }

    private function setUpMultiSite()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
        ]]);
        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/globals-multisite';
        $stache->registerStore((new GlobalsStore($stache, app('files')))->directory($this->directory));
        $stache->registerStore((new GlobalVariablesStore($stache, app('files')))->directory($this->directory));

        $this->repo = new GlobalVariablesRepository($stache);
        $this->globalRepo = new GlobalRepository($stache);
    }

    /** @test */
    public function it_gets_all_global_variables_with_single_site()
    {
        $this->setUpSingleSite();

        $vars = $this->repo->all();

        $this->assertInstanceOf(VariablesCollection::class, $vars);
        $this->assertCount(2, $vars);
        $this->assertEveryItemIsInstanceOf(Variables::class, $vars);

        $ordered = $vars->sortBy->path()->values();
        $this->assertEquals(['contact::en', 'global::en'], $ordered->map->id()->all());
        $this->assertEquals(['contact', 'global'], $ordered->map->handle()->all());
    }

    /** @test */
    public function it_gets_all_global_variables_with_multi_site()
    {
        $this->setUpMultiSite();

        $sets = $this->repo->all();

        $this->assertInstanceOf(VariablesCollection::class, $sets);
        $this->assertCount(4, $sets);
        $this->assertEveryItemIsInstanceOf(Variables::class, $sets);

        $ordered = $sets->sortBy->path()->values();
        $this->assertEquals(['contact::en', 'global::en', 'contact::fr', 'global::fr'], $ordered->map->id()->all());
        $this->assertEquals(['contact', 'global', 'contact', 'global'], $ordered->map->handle()->all());
    }

    /** @test */
    public function it_gets_a_global_variable_by_id_with_single_site()
    {
        $this->setUpSingleSite();

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
    public function it_gets_a_global_variable_by_id_with_multi_site()
    {
        $this->setUpMultiSite();

        tap($this->repo->find('global::en'), function ($variable) {
            $this->assertInstanceOf(Variables::class, $variable);
            $this->assertEquals('global::en', $variable->id());
            $this->assertEquals('global', $variable->handle());
        });

        tap($this->repo->find('global::fr'), function ($variable) {
            $this->assertInstanceOf(Variables::class, $variable);
            $this->assertEquals('global::fr', $variable->id());
            $this->assertEquals('global', $variable->handle());
        });

        $this->assertNull($this->repo->find('global::de'));

        tap($this->repo->find('contact::en'), function ($variable) {
            $this->assertInstanceOf(Variables::class, $variable);
            $this->assertEquals('contact::en', $variable->id());
            $this->assertEquals('contact', $variable->handle());
        });

        tap($this->repo->find('contact::fr'), function ($variable) {
            $this->assertInstanceOf(Variables::class, $variable);
            $this->assertEquals('contact::fr', $variable->id());
            $this->assertEquals('contact', $variable->handle());
        });

        $this->assertNull($this->repo->find('contact::de'));

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    public function it_gets_global_variables_by_set_handle_with_single_site()
    {
        $this->setUpSingleSite();

        tap($this->repo->findBySet('global'), function ($variables) {
            $this->assertInstanceOf(VariablesCollection::class, $variables);
            $first = $variables->first();
            $this->assertEquals('global::en', $first->id());
            $this->assertEquals('global', $first->handle());
        });

        tap($this->repo->findBySet('contact'), function ($variables) {
            $this->assertInstanceOf(VariablesCollection::class, $variables);
            $first = $variables->first();
            $this->assertEquals('contact::en', $first->id());
            $this->assertEquals('contact', $first->handle());
        });

        $this->assertCount(0, $this->repo->findBySet('unknown'));
    }

    /** @test */
    public function it_gets_global_variables_by_set_handle_with_multi_site()
    {
        $this->setUpMultiSite();

        tap($this->repo->findBySet('global'), function ($variables) {
            $this->assertInstanceOf(VariablesCollection::class, $variables);
            $ordered = $variables->sortBy->path()->values();
            $this->assertEquals(['global::en',  'global::fr'], $ordered->map->id()->all());
        });

        tap($this->repo->findBySet('contact'), function ($variables) {
            $this->assertInstanceOf(VariablesCollection::class, $variables);
            $ordered = $variables->sortBy->path()->values();
            $this->assertEquals(['contact::en', 'contact::fr'], $ordered->map->id()->all());
        });

        $this->assertCount(0, $this->repo->findBySet('unknown'));
    }

    /** @test */
    public function it_saves_a_global_to_the_stache_and_to_a_file_with_single_site()
    {
        // In single site, the actual global set should get written.
        // There should be no dedicated global variables file.
        // The Variables object should still exist in the store though.
        $this->setUpSingleSite();

        $global = GlobalSet::make('new')->title('Test Global Test');

        $localization = $global->makeLocalization('en')->data(['foo' => 'bar', 'baz' => 'qux']);
        $global->addLocalization($localization);

        $this->assertNull($this->repo->find('new::en'));

        $this->globalRepo->save($global);

        // Delete the global set file so we can test that it's not being written.
        // At this point it exists in the Stache, so deleting the file will have no effect.
        @unlink($this->directory.'/new.yaml');

        $this->repo->save($localization);

        $this->assertNotNull($item = $this->repo->find('new::en'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $item->data()->all());
        $this->assertFileExists($this->directory.'/new.yaml');
        $this->assertFileDoesNotExist($this->directory.'/en/new.yaml');
        $yaml = <<<'YAML'
title: 'Test Global Test'
data:
  foo: bar
  baz: qux

YAML;
        $this->assertEquals($yaml, file_get_contents($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }

    /** @test */
    public function it_saves_a_global_to_the_stache_and_to_a_file_with_multi_site()
    {
        // In multi-site, the global set should not get written.
        // There should be a dedicated global variables file.

        $this->setUpMultiSite();

        $global = GlobalSet::make('new');

        $localization = $global->makeLocalization('en')->data(['foo' => 'bar', 'baz' => 'qux']);
        $global->addLocalization($localization);

        $this->assertNull($this->repo->find('new::en'));

        $this->globalRepo->save($global);

        // Delete the global set file so we can test that it's not being written.
        // At this point it exists in the Stache, so deleting the file will have no effect.
        @unlink($this->directory.'/new.yaml');

        $this->repo->save($localization);

        $this->assertNotNull($item = $this->repo->find('new::en'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $item->data()->all());
        $this->assertFileDoesNotExist($this->directory.'/new.yaml');
        $this->assertFileExists($this->directory.'/en/new.yaml');
        @unlink($this->directory.'/en/new.yaml');
    }
}
