<?php

namespace Tests\Stache;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Loader;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Exceptions\EmptyStacheException;

class LoaderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->createTestStache();

        $this->loader = new Loader($this->stache);
    }

    private function createTestStache()
    {
        $this->stache = (new Stache)->sites(['en', 'es'])->disableBooting();

        $this->stache->registerStore(new class($this->stache, app('files')) extends BasicStore {
            public function key() { return 'one'; }
        });

        $this->stache->registerStore(new class($this->stache, app('files')) extends BasicStore {
            public function key() { return 'two'; }
        });
    }

    /** @test */
    function empty_meta_throws_exception()
    {
        $this->expectException(EmptyStacheException::class);

        Cache::shouldReceive('has')->with('stache::meta/one')->andReturnFalse();

        $this->loader->load();
    }

    /** @test */
    function it_loads_paths_into_respective_stores()
    {
        Cache::shouldReceive('has')->with('stache::meta/one')->andReturnTrue();
        Cache::shouldReceive('get')->with('stache::meta/one', Mockery::any())->andReturn([
            'uris' => [],
            'paths' => [
                'en' => [
                    12 => 'one/two.md',
                    34 => 'three/four.md',
                ]
            ]
        ]);
        Cache::shouldReceive('has')->with('stache::meta/two')->andReturnTrue();
        Cache::shouldReceive('get')->with('stache::meta/two', Mockery::any())->andReturn([
            'uris' => [],
            'paths' => [
                'en' => [
                    56 => 'five/six.md',
                    78 => 'seven/eight.md',
                ]
            ]
        ]);

        $this->loader->load();

        $this->assertEquals(
            [12 => 'one/two.md', 34 => 'three/four.md'],
            $this->stache->store('one')->getSitePaths('en')->all()
        );

        $this->assertEquals(
            [56 => 'five/six.md', 78 => 'seven/eight.md'],
            $this->stache->store('two')->getSitePaths('en')->all()
        );
    }

    /** @test */
    function it_loads_uris_into_respective_stores()
    {
        Cache::shouldReceive('has')->with('stache::meta/one')->andReturnTrue();
        Cache::shouldReceive('get')->with('stache::meta/one', Mockery::any())->andReturn([
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
        Cache::shouldReceive('has')->with('stache::meta/two')->andReturnTrue();
        Cache::shouldReceive('get')->with('stache::meta/two', Mockery::any())->andReturn([
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

        $this->assertEquals(
            [56 => '/five/six', 78 => '/seven/eight'],
            $this->stache->store('two')->getSiteUris('en')->all()
        );

        $this->assertEquals(
            [56 => '/cinco/seis', 78 => '/siete/ocho'],
            $this->stache->store('two')->getSiteUris('es')->all()
        );
    }

    /** @test */
    function gets_meta_data_from_cache()
    {
        $stache = (new Stache)->sites(['en'])->disableBooting();
        $stache->registerStore(new class($stache, app('files')) extends BasicStore {
            public function key()
            {
                return 'one';
            }
            public function cacheHasMeta() {
                return true;
            }
            public function getMetaFromCache()
            {
                return ['one' => 'first meta data'];
            }
        });
        $stache->registerStore(new class($stache, app('files')) extends BasicStore {
            public function key()
            {
                return 'two';
            }
            public function cacheHasMeta() {
                return true;
            }
            public function getMetaFromCache()
            {
                return ['two' => 'second meta data'];
            }
        });
        $loader = new Loader($stache);

        $meta = $loader->getMetaFromCache();

        $this->assertInstanceOf(Collection::class, $meta);
        $this->assertEquals([
            'one' => 'first meta data',
            'two' => 'second meta data',
        ], $meta->all());
    }

    /** @test */
    function a_store_with_no_meta_data_throws_an_exception()
    {
        $this->expectException(EmptyStacheException::class);

        $stache = (new Stache)->sites(['en'])->disableBooting();
        $stache->registerStore(new class($stache, app('files')) extends BasicStore {
            public function key()
            {
                return 'one';
            }
            public function getMetaFromCache()
            {
                return ['one' => 'first meta data'];
            }
        });
        $stache->registerStore(new class($stache, app('files')) extends BasicStore {
            public function key()
            {
                return 'two';
            }
            public function getMetaFromCache()
            {
                return [];
            }
        });
        $loader = new Loader($stache);

        $loader->getMetaFromCache();
    }
}
