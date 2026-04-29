<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\User\PasskeyLoginController as Controller;

class PasskeyLoginController extends Controller
{
    protected function successRedirectUrl(Request $request): string
    {
        return $request->session()->pull('url.intended', cp_route('index'));
    }
}
