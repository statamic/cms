<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Response;

class AddHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->header('Cache-Control', 'no-store');
        }

        return $response;
    }
}
