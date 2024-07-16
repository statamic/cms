<?php

namespace Tests\Console\Commands;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
}
