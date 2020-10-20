<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\ResetsPasswords;
use Statamic\Http\Middleware\RedirectIfAuthenticated;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    public function __construct()
    {
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('statamic::auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
            'action' => $this->resetFormAction(),
            'title' => $this->resetFormTitle(),
        ]);
    }

    protected function resetFormAction()
    {
        return route('statamic.password.reset.action');
    }

    protected function resetFormTitle()
    {
        return __('Reset Password');
    }

    public function redirectPath()
    {
        return request('redirect') ?? route('statamic.site');
    }

    protected function setUserPassword($user, $password)
    {
        $user->password($password);
    }

    public function broker()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        return Password::broker($broker);
    }
}
