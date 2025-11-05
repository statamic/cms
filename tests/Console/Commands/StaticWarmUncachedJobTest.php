<?php

namespace Tests\Console\Commands;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StaticWarmUncachedJob;
use Statamic\StaticCaching\Cacher;
use Tests\TestCase;

class StaticWarmUncachedJobTest extends TestCase
{
    #[Test]
    public function it_sends_a_get_request()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $job = new StaticWarmUncachedJob(new Request('GET', '/about'), ['handler' => $handlerStack]);

        $job->handle();

        $this->assertEquals('/about', $mock->getLastRequest()->getUri()->getPath());
    }

    #[Test]
    public function it_does_not_send_a_request_if_the_page_is_cached()
    {
        $mockCacher = Mockery::mock(Cacher::class);
        $mockCacher->shouldReceive('hasCachedPage')->once()->andReturn(true);
        $mockCacher->allows('isExcluded')->andReturn(false);
        app()->instance(Cacher::class, $mockCacher);

        $mock = new MockHandler([
            new Response(200),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $job = new StaticWarmUncachedJob(new Request('GET', '/about'), ['handler' => $handlerStack]);

        $job->handle();

        $this->assertNull($mock->getLastRequest());
    }
}
