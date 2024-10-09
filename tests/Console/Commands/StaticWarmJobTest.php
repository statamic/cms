<?php

namespace Tests\Console\Commands;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
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

        $job = new StaticWarmJob(new Request('GET', '/about'), ['handler' => $handlerStack]);

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

        $job = new StaticWarmJob(new Request('GET', '/blog'), ['handler' => $handlerStack]);

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
}
