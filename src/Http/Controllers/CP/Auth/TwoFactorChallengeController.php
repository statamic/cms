<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Auth\TwoFactor\ChallengeTwoFactorAuthentication;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Http\Requests\TwoFactorChallengeRequest;
use Statamic\Support\Str;

class TwoFactorChallengeController extends CpController
{
    public function __construct(Request $request)
    {
        $this->middleware(RedirectIfAuthorized::class);
    }

    public function index(TwoFactorChallengeRequest $request)
    {
        if (! $request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('statamic.cp.login'));
        }

        return view('statamic::auth.two-factor.challenge', [
            'hasError' => $this->hasError(),
            'mode' => session()->get('errors')?->getBag('default')->has('recovery_code') ? 'recovery_code' : 'code',
        ]);
    }

    public function store(TwoFactorChallengeRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        } elseif (! $request->hasValidCode()) {
            return $request->sendFailedTwoFactorChallengeResponse();
        }

        Auth::guard()->login($user, $request->remember());

        $user->setLastTwoFactorChallenged();

        $request->session()->regenerate();

        return $request->expectsJson()
            ? response('Authenticated')
            : redirect()->intended($this->redirectPath());
    }

    private function redirectPath()
    {
        $cp = cp_route('index');
        $referer = request('referer');
        $referredFromCp = Str::startsWith($referer, $cp) && ! Str::startsWith($referer, $cp.'/auth/');

        return $referredFromCp ? $referer : $cp;
    }

    private function hasError()
    {
        return function ($field) {
            if (! $error = optional(session('errors'))->first($field)) {
                return false;
            }

            return ! in_array($error, [
                __('auth.failed'),
                __('statamic::validation.required'),
            ]);
        };
    }
}
