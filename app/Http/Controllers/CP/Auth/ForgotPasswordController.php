<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Statamic\Http\Middleware\CP\RedirectIfAuthenticated;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function showLinkRequestForm()
    {
        return view('statamic::auth.passwords.email');
    }

    public function redirectPath()
    {
        return cp_route('index');
    }
}
