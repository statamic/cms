<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Http\Controllers\ResetPasswordController as Controller;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;

class ResetPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfAuthorized::class);
    }

    public function broker()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        if (is_array($broker)) {
            $broker = $broker['cp'];
        }

        return Password::broker($broker);
    }

    protected function resetFormAction()
    {
        return route('statamic.cp.password.reset.action');
    }
}
