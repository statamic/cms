<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\ResetsPasswords;
use Statamic\Contracts\Auth\User;
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
        // The Statamic user class has a password method that will hash a given plain
        // text password. If we're using the "statamic" user provider, we'll get a
        // Statamic user. Otherwise (i.e. using the "eloquent" provider), we'd
        // just a User model, which requires the password to be pre-hashed.
        if ($user instanceof User) {
            $user->password($password);
        } else {
            $user->password = Hash::make($password);
        }
    }

    public function broker()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        if (is_array($broker)) {
            $broker = $broker['web'];
        }

        return Password::broker($broker);
    }
}
