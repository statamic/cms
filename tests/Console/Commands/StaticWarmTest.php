<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class StaticWarmTest extends TestCase
{
    /** @test */
    public function it_exits_with_error_when_static_caching_is_disabled()
    {
        $this->artisan('statamic:static:warm')
            ->expectsOutput('Static caching is not enabled.')
            ->assertFailed();
    }

    /** @test */
    public function it_warms_the_static_cache()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm')
            ->expectsOutput('Visiting 0 URLs...')
            ->assertSuccessful();
        // Artisan::call('statamic:static:warm');
        // dd(Artisan::output());
    }

    /** @test */
    public function it_doesnt_queue_the_requests_when_connection_is_set_to_sync()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->expectsOutput('The queue connection is set to "sync". Queueing will be disabled.')
            ->assertSuccessful();
    }

    /** @test */
    public function it_queues_the_requests()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'queue.default' => 'redis',
        ]);

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->doesntExpectOutput('The queue connection is set to "sync". Queueing will be disabled.')
            ->assertSuccessful();
    }
}
