<?php

namespace Statamic\Http\Controllers\CP\Auth;

class UnauthorizedController
{
    public function __invoke()
    {
        $redirect = config('statamic.cp.auth.enabled', true)
            ? cp_route('login')
            : config('statamic.cp.auth.redirect_to', '/');

        return view('statamic::auth.unauthorized', compact('redirect'));
    }
}
