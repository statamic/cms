<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Inertia\Inertia;

class UnauthorizedController
{
    public function __invoke(Request $request)
    {
        $redirect = config('statamic.cp.auth.enabled', true)
            ? cp_route('login')
            : config('statamic.cp.auth.redirect_to', '/');

        return Inertia::render('auth/Unauthorized', [
            'isLoggedIn' => (bool) $request->user(),
            'loginUrl' => cp_route('login'),
            'logoutUrl' => cp_route('logout').'?redirect='.$redirect,
        ]);
    }
}
