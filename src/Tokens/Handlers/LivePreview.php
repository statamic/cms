<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\LivePreview as Facade;
use Statamic\Contracts\Tokens\Token;

class LivePreview
{
    public function handle(Token $token, $request, Closure $next)
    {
        $item = Facade::item($token);

        $item->repository()->substitute($item);

        $response = $next($request);

        $response->headers->set('X-Statamic-Live-Preview', true);

        return $response;
    }
}
