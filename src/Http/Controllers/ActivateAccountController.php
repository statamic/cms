<?php

namespace Statamic\Http\Controllers;

use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;

class ActivateAccountController extends ResetPasswordController
{
    public function __construct()
    {
        $this->middleware(RedirectIfAuthorized::class);
    }

    protected function resetFormAction()
    {
        return route('statamic.account.activate.action');
    }

    protected function resetFormTitle()
    {
        return __('Activate Account');
    }

    public function broker()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_ACTIVATIONS);

        return Password::broker($broker);
    }
}
