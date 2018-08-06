<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Loader;
use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\EmptyStacheException;

class LoaderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createTestStache();

        $this->loader = new Loader($this->stache);
    }

    private function createTestStache()
    {
        $this->stache = (new Stache)->sites(['en', 'es']);

        $this->stache->registerStore(new class($this->stache) extends BasicStore {
            public function key() { return 'one'; }
            public function getItemsFromCache($cache) { }
        });

        $this->stache->registerStore(new class($this->stache) extends BasicStore {
            public function key() { return 'two'; }
            public function getItemsFromCache($cache) { }
        });
    }

    /** @test */
    function empty_meta_throws_exception()
    {
        $this->expectException(EmptyStacheException::class);

        Cache::shouldReceive('get')->with('stache::meta/one')->andReturnNull();
        Cache::shouldReceive('get')->with('stache::meta/two')->andReturnNull();

        $this->loader->load();
    }

    /** @test */
    function it_loads_meta_in_the_stache()
    {
        Cache::shouldReceive('get')->with('stache::meta/one')->andReturn($metaOne = [
            'paths' => ['one-test.md'],
            'uris' => ['en' => ['/one-test']],
        ]);
        Cache::shouldReceive('get')->with('stache::meta/two')->andReturn($metaTwo = [
            'paths' => ['two-test.md'],
            'uris' => ['en' => ['/two-test']],
        ]);

        $this->loader->load();

        $this->assertEquals([
            'one' => $metaOne,
            'two' => $metaTwo
        ], $this->stache->meta()->all());
    }

    /** @test */
    function it_loads_paths_into_respective_stores()
    {
        Cache::shouldReceive('get')->with('stache::meta/one')->andReturn([
            'uris' => [],
            'paths' => [
                12 => 'one/two.md',
                34 => 'three/four.md',
            ]
        ]);
        Cache::shouldReceive('get')->with('stache::meta/two')->andReturn([
            'uris' => [],
            'paths' => [
                56 => 'five/six.md',
                78 => 'seven/eight.md',
            ]
        ]);

        $this->loader->load();

        $this->assertEquals(
            [12 => 'one/two.md', 34 => 'three/four.md'],
            $this->stache->store('one')->getPaths()->all()
        );

        $this->assertEquals([
            56 => 'five/six.md', 78 => 'seven/eight.md'],
            $this->stache->store('two')->getPaths()->all()
        );
    }

    /** @test */
    function it_loads_uris_into_respective_stores()
    {
        Cache::shouldReceive('get')->with('stache::meta/one')->andReturn([
            'paths' => [],
            'uris' => [
                'en' => [
                    12 => '/one/two',
                    34 => '/three/four',
                ],
                'es' => [
                    12 => '/uno/dos',
                    34 => '/tres/cuatro',
                ]
            ]
        ]);
        Cache::shouldReceive('get')->with('stache::meta/two')->andReturn([
            'paths' => [],
            'uris' => [
                'en' => [
                    56 => '/five/six',
                    78 => '/seven/eight',
                ],
                'es' => [
                    56 => '/cinco/seis',
                    78 => '/siete/ocho',
                ]
            ]
        ]);

        $this->loader->load();

        $this->assertEquals(
            [12 => '/one/two', 34 => '/three/four'],
            $this->stache->store('one')->getSiteUris('en')->all()
        );

        $this->assertEquals(
            [12 => '/uno/dos', 34 => '/tres/cuatro'],
            $this->stache->store('one')->getSiteUris('es')->all()
        );

        $this->assertEquals([
            56 => '/five/six', 78 => '/seven/eight'],
            $this->stache->store('two')->getSiteUris('en')->all()
        );

        $this->assertEquals(
            [56 => '/cinco/seis', 78 => '/siete/ocho'],
            $this->stache->store('two')->getSiteUris('es')->all()
        );
    }
}
