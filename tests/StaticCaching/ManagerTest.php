<?php

namespace Tests\StaticCaching;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\Writer;
use Statamic\StaticCaching\NoCache\DatabaseSession;
use Statamic\StaticCaching\NoCache\Region;
use Statamic\StaticCaching\NoCache\Session;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    #[Test]
    public function application_driver_uses_the_default_cache_store_when_no_custom_store_is_defined()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'statamic.static_caching.strategies.half.driver' => 'application',
        ]);

        $cacher = StaticCache::driver();

        $this->assertInstanceOf(ApplicationCacher::class, $cacher);
        $this->assertSame(Cache::store(), $this->getCacherCache($cacher));
    }

    #[Test]
    public function application_driver_uses_the_custom_static_cache_store_when_defined()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'statamic.static_caching.strategies.half.driver' => 'application',
            'cache.stores.static_cache' => ['driver' => 'array'],
        ]);

        $cacher = StaticCache::driver();

        $this->assertInstanceOf(ApplicationCacher::class, $cacher);
        $this->assertSame(Cache::store('static_cache'), $this->getCacherCache($cacher));
        $this->assertNotSame(Cache::store(), $this->getCacherCache($cacher));
    }

    private function getCacherCache(ApplicationCacher $cacher)
    {
        $property = new \ReflectionProperty($cacher, 'cache');
        $property->setAccessible(true);

        return $property->getValue($cacher);
    }

    #[Test]
    public function it_flushes()
    {
        config([
            'statamic.static_caching.strategy' => 'test',
            'statamic.static_caching.strategies.test.driver' => 'test',
            'cache.stores.static_cache' => [
                'driver' => 'file',
                'path' => storage_path('statamic/static-urls-cache'),
            ],
        ]);

        $mock = Mockery::mock(Cacher::class)->shouldReceive('flush')->once()->getMock();
        StaticCache::extend('test', fn () => $mock);

        Cache::shouldReceive('store')->andReturnSelf();
        Cache::shouldReceive('flush')->once();
        Cache::shouldReceive('forget')->with('statamic:cache-utility:page-statuses')->once();

        StaticCache::flush();
    }

    #[Test]
    public function it_flushes_the_dedicated_store_for_drivers_that_do_not_flush_it_themselves()
    {
        config([
            'statamic.static_caching.strategy' => 'null',
            'cache.stores.static_cache' => ['driver' => 'array'],
        ]);

        Cache::store('static_cache')->forever('nocache::urls', ['/one']);

        StaticCache::flush();

        $this->assertNull(Cache::store('static_cache')->get('nocache::urls'));
    }

    #[Test]
    #[DataProvider('nocacheStorageProvider')]
    public function a_page_cached_while_flushing_keeps_its_nocache_regions($dedicatedStore, $database)
    {
        $path = sys_get_temp_dir().'/statamic-manager-'.uniqid();
        $url = 'http://example.com/one';
        $regionKey = null;

        config([
            'statamic.static_caching.strategy' => 'test',
            'statamic.static_caching.strategies.test.driver' => 'test',
            'statamic.static_caching.nocache' => $database ? 'database' : 'cache',
        ]);

        if ($dedicatedStore) {
            config(['cache.stores.static_cache' => ['driver' => 'array']]);
        }

        if ($database) {
            Schema::create('nocache_regions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('url')->index();
                $table->longText('region');
                $table->timestamps();
            });
        }

        $sessionClass = $database ? DatabaseSession::class : Session::class;

        // Stands in for a request that finishes rendering while the flush is deleting files.
        $writer = new class extends Writer
        {
            public $concurrentRequest;

            public function flush($path)
            {
                parent::flush($path);

                ($this->concurrentRequest)();
            }
        };

        $cacher = new FileCacher(
            $writer,
            StaticCache::cacheStore(),
            ['path' => $path, 'base_url' => 'http://example.com', 'lock_hold_length' => 0, 'locale' => 'en'],
        );

        StaticCache::extend('test', fn () => $cacher);

        $writer->concurrentRequest = function () use ($cacher, $url, $sessionClass, &$regionKey) {
            $session = new $sessionClass($url);
            $regionKey = $session->pushRegion('Log in', [], 'antlers.html')->key();

            $cacher->cachePage(Request::create($url), 'html');
            $session->write();
        };

        try {
            StaticCache::flush();

            $this->assertFileExists($path.'/one_.html');
            $this->assertInstanceOf(Region::class, (new $sessionClass($url))->restore()->region($regionKey));
        } finally {
            File::deleteDirectory($path);
            Schema::dropIfExists('nocache_regions');
        }
    }

    public static function nocacheStorageProvider()
    {
        return [
            'shared store' => [false, false],
            'dedicated store' => [true, false],
            'database' => [false, true],
        ];
    }

    #[Test]
    public function it_flushes_without_static_cache_store()
    {
        config([
            'statamic.static_caching.strategy' => 'test',
            'statamic.static_caching.strategies.test.driver' => 'test',
        ]);

        $mock = Mockery::mock(Cacher::class)->shouldReceive('flush')->once()->getMock();
        StaticCache::extend('test', fn () => $mock);

        Cache::shouldReceive('store')->andReturnSelf();
        Cache::shouldReceive('get')->with('nocache::urls', [])->once()->andReturn(['/one', '/two']);
        Cache::shouldReceive('get')->with('nocache::session.'.md5('/one'))->once()->andReturn(['regions' => ['r1', 'r2']]);
        Cache::shouldReceive('get')->with('nocache::session.'.md5('/two'))->once()->andReturn(['regions' => ['r3', 'r4']]);
        Cache::shouldReceive('forget')->with('nocache::region.r1')->once();
        Cache::shouldReceive('forget')->with('nocache::region.r2')->once();
        Cache::shouldReceive('forget')->with('nocache::region.r3')->once();
        Cache::shouldReceive('forget')->with('nocache::region.r4')->once();
        Cache::shouldReceive('forget')->with('nocache::session.'.md5('/one'))->once();
        Cache::shouldReceive('forget')->with('nocache::session.'.md5('/two'))->once();
        Cache::shouldReceive('forget')->with('nocache::urls')->once();
        Cache::shouldReceive('forget')->with('statamic:cache-utility:page-statuses')->once();

        StaticCache::flush();
    }
}
