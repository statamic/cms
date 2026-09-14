<?php

namespace Tests\Console\Commands;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\Concerns\NormalizesPaginationHeader;
use Statamic\Console\Commands\StaticWarmJob;
use Tests\TestCase;

class StaticWarmJobTest extends TestCase
{
    #[Test]
    public function it_sends_a_get_request()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $job = new StaticWarmJob(new Request('GET', 'http://localhost/about'), ['handler' => $handlerStack]);

        $job->handle();

        $this->assertEquals('/about', $mock->getLastRequest()->getUri()->getPath());
    }

    #[Test]
    public function it_sends_a_get_request_and_dispatches_static_warm_job_for_page_with_pagination()
    {
        Queue::fake();

        $mock = new MockHandler([
            (new Response(200))->withHeader('X-Statamic-Pagination', [
                'current' => 1,
                'total' => 3,
                'name' => 'page',
            ]),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $job = new StaticWarmJob(new Request('GET', 'http://localhost/blog'), ['handler' => $handlerStack]);

        $job->handle();

        $this->assertEquals('/blog', $mock->getLastRequest()->getUri()->getPath());

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getPath() === '/blog'
                && $job->request->getUri()->getQuery() === 'page=1';
        });

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getPath() === '/blog'
                && $job->request->getUri()->getQuery() === 'page=2';
        });

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getPath() === '/blog'
                && $job->request->getUri()->getQuery() === 'page=3';
        });
    }

    #[Test]
    public function subsequent_paginated_pages_dont_dispatch_static_warm_jobs()
    {
        Queue::fake();

        $mock = new MockHandler([
            (new Response(200))->withHeader('X-Statamic-Pagination', [
                'current' => 2,
                'total' => 3,
                'name' => 'page',
            ]),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $job = new StaticWarmJob(new Request('GET', 'http://localhost/blog?page=2'), ['handler' => $handlerStack]);

        $job->handle();

        $this->assertEquals('/blog', $mock->getLastRequest()->getUri()->getPath());

        // The first page is responsible for dispatchin jobs. Not subsequent pages.
        Queue::assertNothingPushed();
    }

    #[Test]
    public function it_dispatches_paginated_jobs_when_the_pagination_header_is_folded_into_one_line()
    {
        Queue::fake();

        // A proxy or CDN may coalesce the repeated header into a single comma-joined line.
        $mock = new MockHandler([
            (new Response(200))->withHeader('X-Statamic-Pagination', '1, 3, page'),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $job = new StaticWarmJob(new Request('GET', 'http://localhost/blog'), ['handler' => $handlerStack]);

        $job->handle();

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getQuery() === 'page=1';
        });

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getQuery() === 'page=2';
        });

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getQuery() === 'page=3';
        });
    }

    #[Test]
    public function it_keeps_a_page_name_that_contains_a_comma()
    {
        $parser = new class
        {
            use NormalizesPaginationHeader;

            public function parse($response)
            {
                return $this->paginationHeader($response);
            }
        };

        // Three separate header values, as set by the static caching middleware.
        $separate = (new Response(200))->withHeader('X-Statamic-Pagination', ['current' => 1, 'total' => 3, 'name' => 'pa,ge']);
        $this->assertSame([1, 3, 'pa,ge'], $parser->parse($separate));

        // The same header folded into one comma-joined line by a proxy.
        $folded = (new Response(200))->withHeader('X-Statamic-Pagination', '1, 3, pa,ge');
        $this->assertSame([1, 3, 'pa,ge'], $parser->parse($folded));
    }
}
