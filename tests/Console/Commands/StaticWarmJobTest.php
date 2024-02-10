<?php

namespace Tests\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use Statamic\Console\Commands\StaticWarmJob;
use Tests\TestCase;

class StaticWarmJobTest extends TestCase
{
    /** @test */
    public function it_sends_a_get_request()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);

        $job = new StaticWarmJob(new Request('GET', '/about'));

        $job->handle($client);

        $this->assertEquals('/about', $mock->getLastRequest()->getUri()->getPath());
    }

    /** @test */
    public function it_sends_a_get_request_and_dispatches_static_warm_job_for_page_with_pagination()
    {
        Queue::fake();

        $mock = new MockHandler([
            (new Response(200))->withHeader('Statamic-Pagination-Next', '/blog?page=2'),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);

        $job = new StaticWarmJob(new Request('GET', '/blog'));

        $job->handle($client);

        $this->assertEquals('/blog', $mock->getLastRequest()->getUri()->getPath());

        Queue::assertPushed(StaticWarmJob::class, function (StaticWarmJob $job) {
            return $job->request->getUri()->getPath() === '/blog'
                && $job->request->getUri()->getQuery() === 'page=2';
        });
    }
}
