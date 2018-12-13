<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Statamic\Http\Middleware\CP\RedirectIfAuthenticated;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords {
        resetPassword as protected traitResetPassword;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        return cp_route('index');
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
