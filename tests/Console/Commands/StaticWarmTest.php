<?php

namespace Tests\Console\Commands;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Queue;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StaticWarmTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->createPage('about');
        $this->createPage('contact');
    }

    /** @test */
    public function it_exits_with_error_when_static_caching_is_disabled()
    {
        $this->artisan('statamic:static:warm')
            ->expectsOutput('Static caching is not enabled.')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_warms_the_static_cache()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm')
            ->expectsOutput('Visiting 2 URLs...')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_doesnt_queue_the_requests_when_connection_is_set_to_sync()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->expectsOutput('The queue connection is set to "sync". Queueing will be disabled.')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_queues_the_requests()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'queue.default' => 'redis',
        ]);

        Queue::fake();

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->expectsOutput('Queueing 2 requests...')
            ->assertExitCode(0);

        Queue::assertPushed(StaticWarmJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/about';
        });
        Queue::assertPushed(StaticWarmJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/contact';
        });
    }

    private function createPage($slug, $attributes = [])
    {
        $this->makeCollection()->save();

        return tap($this->makePage($slug, $attributes))->save();
    }

    private function makePage($slug, $attributes = [])
    {
        return EntryFactory::slug($slug)
            ->id($slug)
            ->collection('pages')
            ->data($attributes['with'] ?? [])
            ->make();
    }

    private function makeCollection()
    {
        return Collection::make('pages')
            ->routes('{slug}')
            ->template('default');
    }
}
