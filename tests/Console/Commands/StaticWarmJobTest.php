<?php

namespace Tests\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
}
