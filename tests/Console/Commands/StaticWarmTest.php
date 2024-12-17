<?php

namespace Tests\Console\Commands;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Queue;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Console\Commands\StaticWarmUncachedJob;
use Statamic\Facades\Collection;
use Statamic\StaticCaching\Cacher;
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

    #[Test]
    public function it_exits_with_error_when_static_caching_is_disabled()
    {
        $this->artisan('statamic:static:warm')
            ->expectsOutputToContain('Static caching is not enabled.')
            ->assertExitCode(1);
    }

    #[Test]
    public function it_warms_the_static_cache()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm')
            ->expectsOutput('Visiting 2 URLs...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_only_visits_uncached_urls_when_the_uncached_option_is_used()
    {
        $mock = Mockery::mock(Cacher::class);
        $mock->shouldReceive('hasCachedPage')->times(2)->andReturn(true, false);
        $mock->allows('isExcluded')->andReturn(false);
        app()->instance(Cacher::class, $mock);

        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm', ['--uncached' => true])
            ->expectsOutput('Visiting 1 URLs...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_only_visits_included_urls()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->createPage('blog');
        $this->createPage('news');

        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->template('default')
            ->save();

        Collection::make('news')
            ->routes('/news/{slug}')
            ->template('default')
            ->save();

        EntryFactory::slug('post-1')->collection('blog')->id('blog-post-1')->create();
        EntryFactory::slug('post-2')->collection('blog')->id('blog-post-2')->create();
        EntryFactory::slug('article-1')->collection('news')->id('news-article-1')->create();
        EntryFactory::slug('article-2')->collection('news')->id('news-article-2')->create();
        EntryFactory::slug('article-3')->collection('news')->id('news-article-3')->create();

        $this->artisan('statamic:static:warm', ['--include' => '/blog/post-1,/news/*'])
            ->expectsOutput('Visiting 4 URLs...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_doesnt_visit_excluded_urls()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->createPage('blog');
        $this->createPage('news');

        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->template('default')
            ->save();

        Collection::make('news')
            ->routes('/news/{slug}')
            ->template('default')
            ->save();

        EntryFactory::slug('post-1')->collection('blog')->id('blog-post-1')->create();
        EntryFactory::slug('post-2')->collection('blog')->id('blog-post-2')->create();
        EntryFactory::slug('article-1')->collection('news')->id('news-article-1')->create();
        EntryFactory::slug('article-2')->collection('news')->id('news-article-2')->create();
        EntryFactory::slug('article-3')->collection('news')->id('news-article-3')->create();

        $this->artisan('statamic:static:warm', ['--exclude' => '/about,/contact,/blog/*,/news/article-2'])
            ->expectsOutput('Visiting 4 URLs...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_respects_max_depth()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        Collection::make('blog')
            ->routes('/awesome/blog/{slug}')
            ->template('default')
            ->save();

        Collection::make('news')
            ->routes('/news/{slug}')
            ->template('default')
            ->save();

        EntryFactory::slug('post-1')->collection('blog')->id('blog-post-1')->create();
        EntryFactory::slug('post-2')->collection('blog')->id('blog-post-2')->create();
        EntryFactory::slug('post-3')->collection('blog')->id('blog-post-3')->create();
        EntryFactory::slug('article-1')->collection('news')->id('news-article-1')->create();

        $this->artisan('statamic:static:warm', ['--max-depth' => 2])
            ->expectsOutput('Visiting 3 URLs...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_doesnt_queue_the_requests_when_connection_is_set_to_sync()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->expectsOutputToContain('The queue connection is set to "sync". Queueing will be disabled.')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_queues_the_requests()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'queue.default' => 'redis',
        ]);

        Queue::fake();

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->expectsOutputToContain('Adding 2 requests')
            ->assertExitCode(0);

        Queue::assertPushed(StaticWarmJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/about';
        });
        Queue::assertPushed(StaticWarmJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/contact';
        });
    }

    #[Test]
    public function it_queues_the_request_when_the_uncached_option_is_used()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'queue.default' => 'redis',
        ]);

        Queue::fake();

        $this->artisan('statamic:static:warm', ['--queue' => true, '--uncached' => true])
            ->expectsOutputToContain('Adding 2 requests')
            ->assertExitCode(0);

        Queue::assertCount(2);

        Queue::assertPushed(StaticWarmUncachedJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/about';
        });
        Queue::assertPushed(StaticWarmUncachedJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/contact';
        });
    }

    #[Test]
    public function it_doesnt_queue_the_request_when_the_uncached_option_is_used_and_the_page_is_cached()
    {
        config([
            'statamic.static_caching.strategy' => 'half',
            'queue.default' => 'redis',
        ]);

        $mock = Mockery::mock(Cacher::class);
        $mock->shouldReceive('hasCachedPage')->times(2)->andReturn(true, false);
        $mock->allows('isExcluded')->andReturn(false);
        app()->instance(Cacher::class, $mock);

        Queue::fake();

        $this->artisan('statamic:static:warm', ['--queue' => true, '--uncached' => true])
            ->expectsOutputToContain('Adding 1 requests')
            ->assertExitCode(0);

        Queue::assertCount(1);

        Queue::assertNotPushed(StaticWarmUncachedJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/about';
        });
        Queue::assertPushed(StaticWarmUncachedJob::class, function ($job) {
            return $job->request->getUri()->getPath() === '/contact';
        });
    }

    #[Test, DataProvider('queueConnectionsProvider')]
    public function it_queues_the_requests_with_appropriate_queue_and_connection(
        $configuredQueue,
        $configuredConnection,
        $defaultConnection,
        $expectedJobQueue,
        $expectedJobConnection
    ) {
        config([
            'statamic.static_caching.strategy' => 'half',
            'statamic.static_caching.warm_queue' => $configuredQueue,
            'statamic.static_caching.warm_queue_connection' => $configuredConnection,
            'queue.default' => $defaultConnection,
        ]);

        Queue::fake();

        $this->artisan('statamic:static:warm', ['--queue' => true])
            ->expectsOutputToContain('Adding 2 requests')
            ->assertExitCode(0);

        Queue::assertPushed(StaticWarmJob::class, fn ($job) => $job->connection === $expectedJobConnection && $job->queue === $expectedJobQueue);
    }

    public static function queueConnectionsProvider()
    {
        return [
            [null, null, 'redis', null, 'redis'],
            ['warm', null, 'redis', 'warm', 'redis'],
            [null, 'sqs', 'redis', null, 'sqs'],
            ['warm', 'sqs', 'redis', 'warm', 'sqs'],
        ];
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
