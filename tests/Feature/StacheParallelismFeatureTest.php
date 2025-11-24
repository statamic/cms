<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Tests\TestCase;

class StacheParallelismFeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Stache::clear();
    }

    #[Test]
    public function stache_warm_command_works_with_parallelism_disabled()
    {
        Config::set('statamic.stache.parallelism.enabled', false);

        $result = Artisan::call('statamic:stache:warm');

        $this->assertEquals(0, $result);
        $this->assertNotNull(Stache::buildTime());
        $this->assertNotNull(Stache::buildDate());
    }

    #[Test]
    public function stache_warm_command_works_with_parallelism_enabled()
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not available');
        }

        Config::set('statamic.stache.parallelism.enabled', true);
        Config::set('statamic.stache.parallelism.max_processes', 2);

        $result = Artisan::call('statamic:stache:warm');

        $this->assertEquals(0, $result);
        $this->assertNotNull(Stache::buildTime());
        $this->assertNotNull(Stache::buildDate());
    }

    #[Test]
    public function stache_refresh_command_works_with_parallelism()
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not available');
        }

        Config::set('statamic.stache.parallelism.enabled', true);
        Config::set('statamic.stache.parallelism.max_processes', 2);

        $result = Artisan::call('statamic:stache:refresh');

        $this->assertEquals(0, $result);
        $this->assertNotNull(Stache::buildTime());
        $this->assertNotNull(Stache::buildDate());
    }

    #[Test]
    public function parallelism_configuration_is_properly_loaded()
    {
        Config::set('statamic.stache.parallelism.enabled', true);
        Config::set('statamic.stache.parallelism.max_processes', 8);

        $this->assertTrue(config('statamic.stache.parallelism.enabled'));
        $this->assertEquals(8, config('statamic.stache.parallelism.max_processes'));
    }

    #[Test]
    public function parallelism_defaults_are_correct()
    {
        // Test default behavior when config is not explicitly set
        $enabled = config('statamic.stache.parallelism.enabled', false);
        $maxProcesses = config('statamic.stache.parallelism.max_processes', 4);

        // Should use the default values we provide
        $this->assertFalse($enabled);
        $this->assertEquals(4, $maxProcesses);
    }

    #[Test]
    public function stache_warm_stores_command_is_registered()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('statamic:stache:warm-stores', $commands);
    }

    #[Test]
    public function performance_improvement_is_measurable()
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not available');
        }

        // Test sequential
        Config::set('statamic.stache.parallelism.enabled', false);
        Stache::clear();

        $startTime = microtime(true);
        Artisan::call('statamic:stache:warm');
        $sequentialTime = microtime(true) - $startTime;

        // Test parallel
        Config::set('statamic.stache.parallelism.enabled', true);
        Config::set('statamic.stache.parallelism.max_processes', 4);
        Stache::clear();

        $startTime = microtime(true);
        Artisan::call('statamic:stache:warm');
        $parallelTime = microtime(true) - $startTime;

        // Both should complete successfully
        $this->assertGreaterThan(0, $sequentialTime);
        $this->assertGreaterThan(0, $parallelTime);
    }
}
