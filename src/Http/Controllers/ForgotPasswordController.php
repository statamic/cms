<?php

namespace Statamic\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\SendsPasswordResetEmails;
use Statamic\Facades\URL;
use Statamic\Http\Middleware\RedirectIfAuthenticated;

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
        return Inertia::render('auth/passwords/Email', [
            'action' => cp_route('password.email'),
            'loginUrl' => cp_route('login'),
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        if ($url = $this->getResetFormUrl($request)) {
            PasswordReset::resetFormUrl(URL::makeAbsolute($url));
        }

        return $this->traitSendResetLinkEmail($request);
    }

    private function getResetFormUrl(Request $request): ?string
    {
        if (! $url = $request->_reset_url) {
            return null;
        }

        if (strlen($url) > 2048) {
            return null;
        }

        try {
            $url = decrypt($url);
        } catch (DecryptException $e) {
            if (! str_starts_with($url, '/') || str_starts_with($url, '//')) {
                return null;
            }

            if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
                return null;
            }

            return $url;
        }

        return URL::isExternalToApplication($url) ? null : $url;
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
