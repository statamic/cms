<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Http\Middleware\PingOutpost;
use Statamic\Licensing\Radio;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PingOutpostTest extends TestCase
{
    #[Test]
    public function it_is_registered_as_global_middleware()
    {
        $this->assertTrue(
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->hasMiddleware(PingOutpost::class)
        );
    }

    #[Test]
    public function it_pings_during_cp_requests()
    {
        $request = Request::create('/cp/dashboard');
        $radio = $this->mock(Radio::class);
        $radio->shouldReceive('shouldPingDuringRequest')->once()->with($request)->andReturnTrue();
        $radio->shouldReceive('ping')->once();
        $radio->shouldReceive('shouldPingAfterResponse')->once()->with($request)->andReturnFalse();

        $middleware = new PingOutpost($radio);
        $response = $middleware->handle($request, fn () => new Response);

        $middleware->terminate($request, $response);
    }

    #[Test]
    public function it_pings_after_front_end_responses()
    {
        $request = Request::create('/about');
        $radio = $this->mock(Radio::class);
        $radio->shouldReceive('shouldPingDuringRequest')->once()->with($request)->andReturnFalse();
        $radio->shouldReceive('shouldPingAfterResponse')->once()->with($request)->andReturnTrue();
        $radio->shouldReceive('ping')->once();

        $middleware = new PingOutpost($radio);
        $response = $middleware->handle($request, fn () => new Response);

        $middleware->terminate($request, $response);
    }

    #[Test]
    public function it_does_not_ping_when_radio_declines()
    {
        $request = Request::create('/img/foo.jpg');
        $radio = $this->mock(Radio::class);
        $radio->shouldReceive('shouldPingDuringRequest')->once()->with($request)->andReturnFalse();
        $radio->shouldReceive('shouldPingAfterResponse')->once()->with($request)->andReturnFalse();
        $radio->shouldReceive('ping')->never();

        $middleware = new PingOutpost($radio);
        $response = $middleware->handle($request, fn () => new Response);

        $middleware->terminate($request, $response);
    }
}
