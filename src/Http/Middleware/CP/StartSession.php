<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Session\Middleware\StartSession as Middleware;

class StartSession extends Middleware
{
    protected function saveSession($request)
    {
        if (
            $request->route()->named('statamic.cp.session.timeout')
            && $request->session()->has('last_activity')
        ) {
            return;
        }

        session()->put('last_activity', now()->timestamp);

        parent::saveSession($request);
    }
}
