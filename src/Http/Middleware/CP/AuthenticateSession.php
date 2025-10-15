<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Http\Request;

class AuthenticateSession extends \Illuminate\Session\Middleware\AuthenticateSession
{
    protected function redirectTo(Request $request)
    {
        return cp_route('login');
    }
}
