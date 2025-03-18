<?php

namespace Tests\Stache\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Globals\Variables;
use Statamic\Exceptions\GlobalVariablesNotFoundException;
use Statamic\Facades\GlobalSet;
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
    private $globalRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
        ]);

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/globals';
        $stache->registerStore((new GlobalsStore($stache, app('files')))->directory($this->directory));
        $stache->registerStore((new GlobalVariablesStore($stache, app('files')))->directory($this->directory));

        $this->repo = new GlobalVariablesRepository($stache);
        $this->globalRepo = new GlobalRepository($stache);
    }

    #[Test]
    public function it_gets_all_global_variables()
    {
        $sets = $this->repo->all();

        $this->assertInstanceOf(VariablesCollection::class, $sets);
        $this->assertCount(4, $sets);
        $this->assertEveryItemIsInstanceOf(Variables::class, $sets);

        $ordered = $sets->sortBy->path()->values();
        $this->assertEquals(['contact::en', 'global::en', 'contact::fr', 'global::fr'], $ordered->map->id()->all());
        $this->assertEquals(['contact', 'global', 'contact', 'global'], $ordered->map->handle()->all());
    }

    #[Test]
    public function it_gets_a_global_variable_by_id()
    {
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

    #[Test]
    public function it_gets_global_variables_by_set_handle()
    {
        tap($this->repo->whereSet('global'), function ($variables) {
            $this->assertInstanceOf(VariablesCollection::class, $variables);
            $ordered = $variables->sortBy->path()->values();
            $this->assertEquals(['global::en',  'global::fr'], $ordered->map->id()->all());
        });

        tap($this->repo->whereSet('contact'), function ($variables) {
            $this->assertInstanceOf(VariablesCollection::class, $variables);
            $ordered = $variables->sortBy->path()->values();
            $this->assertEquals(['contact::en', 'contact::fr'], $ordered->map->id()->all());
        });

        $this->assertCount(0, $this->repo->whereSet('unknown'));
    }

    #[Test]
    public function it_saves_a_global_to_the_stache_and_to_a_file()
    {
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

    #[Test]
    public function it_deletes_a_global_from_the_stache_and_file()
    {
        $global = GlobalSet::make('new');
        $localization = $global->makeLocalization('en')->data(['foo' => 'bar', 'baz' => 'qux']);
        $global->addLocalization($localization);
        $this->globalRepo->save($global);
        $this->repo->save($localization);

        $this->assertNotNull($item = $this->repo->find('new::en'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $item->data()->all());

        $this->repo->delete($item);

        $this->assertNull($this->repo->find('new::en'));
        $this->assertNotNull($this->globalRepo->find('new'));
        $this->assertFileDoesNotExist($this->directory.'/en/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function test_find_or_fail_gets_global()
    {
        $var = $this->repo->findOrFail('global::en');

        $this->assertInstanceOf(Variables::class, $var);
        $this->assertEquals('General', $var->title());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_global_does_not_exist()
    {
        $this->expectException(GlobalVariablesNotFoundException::class);
        $this->expectExceptionMessage('Global Variables [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
