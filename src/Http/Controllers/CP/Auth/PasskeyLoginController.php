<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\Http\Controllers\User\PasskeyLoginController as Controller;

class PasskeyLoginController extends Controller
{
    protected function defaultRedirectUrl(): string
    {
        return cp_route('index');
    }
}
