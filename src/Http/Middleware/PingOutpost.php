<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Licensing\Radio;
use Symfony\Component\HttpFoundation\Response;

class PingOutpost
{
    public function __construct(private Radio $radio)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->radio->shouldPingDuringRequest($request)) {
            $this->radio->ping();
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($this->radio->shouldPingAfterResponse($request)) {
            $this->radio->ping();
        }
    }
}
