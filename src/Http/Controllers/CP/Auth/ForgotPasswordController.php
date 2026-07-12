<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Http\Controllers\ForgotPasswordController as Controller;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfAuthorized::class);
    }

    public function sendResetLinkEmail(Request $request)
    {
        PasswordReset::resetFormRoute('statamic.cp.password.reset');
        PasswordReset::redirectAfterReset(route('statamic.cp.index'));

        return parent::sendResetLinkEmail($request);
    }

    public function broker()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        if (is_array($broker)) {
            $broker = $broker['cp'];
        }

        return Password::broker($broker);
    }
}
