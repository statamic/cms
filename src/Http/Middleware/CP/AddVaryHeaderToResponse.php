<?php

namespace Statamic\Http\Middleware\CP;

use Closure;

class AddVaryHeaderToResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Vary', 'X-Requested-With');

        return $response;
    }
}
