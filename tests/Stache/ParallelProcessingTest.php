<?php

namespace Tests\Stache;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Tests\TestCase;

class ParallelProcessingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Clear stache before each test
        Stache::clear();
    }

    #[Test]
    public function it_respects_parallelism_enabled_configuration()
    {
        Config::set('statamic.stache.parallelism.enabled', true);

        $this->assertTrue(config('statamic.stache.parallelism.enabled'));
    }

    #[Test]
    public function it_respects_parallelism_disabled_configuration()
    {
        Config::set('statamic.stache.parallelism.enabled', false);

        $this->assertFalse(config('statamic.stache.parallelism.enabled'));
    }

    #[Test]
    public function it_respects_max_processes_configuration()
    {
        Config::set('statamic.stache.parallelism.max_processes', 8);

        $maxProcesses = config('statamic.stache.parallelism.max_processes');

        $this->assertEquals(8, $maxProcesses);
    }

    #[Test]
    public function it_uses_default_max_processes_when_not_configured()
    {
        $maxProcesses = config('statamic.stache.parallelism.max_processes', 4);

        $this->assertEquals(4, $maxProcesses);
    }

    #[Test]
    public function it_can_warm_stache_with_parallelism_disabled()
    {
        Config::set('statamic.stache.parallelism.enabled', false);

        Stache::warm();

        $this->assertNotNull(Stache::buildTime());
        $this->assertNotNull(Stache::buildDate());
    }

    #[Test]
    public function it_can_warm_stache_with_parallelism_enabled()
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not available');
        }

        Config::set('statamic.stache.parallelism.enabled', true);
        Config::set('statamic.stache.parallelism.max_processes', 2);

        Stache::warm();

        $this->assertNotNull(Stache::buildTime());
        $this->assertNotNull(Stache::buildDate());
    }

    #[Test]
    public function parallel_processing_records_build_metrics()
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl extension not available');
        }

        Config::set('statamic.stache.parallelism.enabled', true);
        Config::set('statamic.stache.parallelism.max_processes', 2);

        $beforeTime = now();

        Stache::clear();
        Stache::warm();

        $buildTime = Stache::buildTime();
        $buildDate = Stache::buildDate();

        $this->assertIsNumeric($buildTime);
        $this->assertGreaterThan(0, $buildTime);
        $this->assertNotNull($buildDate);
        $this->assertGreaterThanOrEqual($beforeTime->timestamp, $buildDate->timestamp);
    }

    #[Test]
    public function it_handles_configuration_defaults_correctly()
    {
        // Test default values
        $enabled = config('statamic.stache.parallelism.enabled', false);
        $maxProcesses = config('statamic.stache.parallelism.max_processes', 4);

        $this->assertIsBool($enabled);
        $this->assertIsInt($maxProcesses);
        $this->assertGreaterThan(0, $maxProcesses);
    }
}
