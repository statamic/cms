<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Http\Controllers\ForgotPasswordController as Controller;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfAuthorized::class);
    }

    public function sendResetLinkEmail(Request $request)
    {
        PasswordReset::redirectAfterReset(route('statamic.cp.index'));

        return parent::sendResetLinkEmail($request);
    }
}
