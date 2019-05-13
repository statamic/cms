<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Session\Middleware\StartSession as Middleware;

class StartSession57 extends Middleware
{
    public function handle($request, Closure $next)
    {
        $response = parent::handle($request, $next);

        session()->put('last_activity', now()->timestamp);

        return $response;
    }

    public function terminate($request, $response)
    {
        if ($request->route()->named('statamic.cp.session.timeout')) {
            return;
        }

        parent::terminate($request, $response);
    }
}
