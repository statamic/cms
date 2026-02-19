<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\SendsPasswordResetEmails;
use Statamic\Exceptions\ValidationException;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Http\Middleware\RedirectIfAuthenticated;
use Statamic\Support\Str;

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
        return view('statamic::auth.passwords.email')->with([
            'title' => __('Forgot Your Password?'),
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        if ($url = $request->_reset_url) {
            $url = URL::makeAbsolute($url);

            $isExternal = Site::all()
                ->map(fn ($site) => $site->absoluteUrl())
                ->filter(fn ($siteUrl) => Str::startsWith($url, $siteUrl))
                ->isEmpty();

            throw_if($isExternal, ValidationException::withMessages([
                '_reset_url' => trans('validation.url', ['attribute' => '_reset_url']),
            ]));

            PasswordReset::resetFormUrl($url);
        }

        return $this->traitSendResetLinkEmail($request);
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
