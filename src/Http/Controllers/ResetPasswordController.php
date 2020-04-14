<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Auth\ResetsPasswords;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Middleware\RedirectIfAuthenticated;

class ResetPasswordController extends Controller
{
    use ResetsPasswords {
        resetPassword as protected traitResetPassword;
    }

    public function __construct()
    {
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('statamic::auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function redirectPath()
    {
        return request('redirect') ?? route('statamic.site');
    }

    protected function resetPassword($user, $password)
    {
        // We override because the parent (trait) method hashes the password first,
        // but the Statamic User class's password method also hashes, which would
        // result in a double-hashed password. Also, it uses the mutator style.
        $user->password($password);

        $this->traitResetPassword($user, $password);
    }
}
