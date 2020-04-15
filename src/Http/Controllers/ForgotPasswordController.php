<?php

namespace Statamic\Http\Controllers;

use Statamic\Facades\URL;
use Illuminate\Http\Request;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Http\Middleware\RedirectIfAuthenticated;
use Statamic\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails {
        sendResetLinkEmail as public traitSendResetLinkEmail;
    }

    public function __construct()
    {
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function showLinkRequestForm()
    {
        return view('statamic::auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        if ($url = $request->reset_url) {
            PasswordReset::resetFormUrl(URL::makeAbsolute($url));
        }

        return $this->traitSendResetLinkEmail($request);
    }
}
