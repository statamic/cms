<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Events\TwoFactorAuthenticationFailed;
use Statamic\Events\ValidTwoFactorAuthenticationCodeProvided;
use Statamic\Http\Middleware\RedirectIfAuthenticated;
use Statamic\Http\Requests\TwoFactorChallengeRequest;

class TwoFactorChallengeController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('throttle:two-factor');
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function index(TwoFactorChallengeRequest $request)
    {
        if (! $request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('statamic.cp.login'));
        }

        return view('statamic::auth.two-factor.challenge', [
            'hasError' => $this->hasError(),
            'action' => $this->formAction(),
            'mode' => session()->get('errors')?->getBag('default')->has('recovery_code') ? 'recovery_code' : 'code',
        ]);
    }

    public function store(TwoFactorChallengeRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceTwoFactorRecoveryCode($code);
        } elseif (! $request->hasValidCode()) {
            TwoFactorAuthenticationFailed::dispatch($user);

            return $request->sendFailedTwoFactorChallengeResponse();
        }

        ValidTwoFactorAuthenticationCodeProvided::dispatch($user);

        Auth::guard()->login($user, $request->remember());

        $request->session()->elevate();

        $request->session()->regenerate();

        return $request->expectsJson()
            ? response('Authenticated')
            : redirect()->intended($this->redirectPath());
    }

    protected function formAction()
    {
        return route('statamic.two-factor-challenge');
    }

    protected function redirectPath()
    {
        return request('redirect') ?? route('statamic.site');
    }

    protected function hasError()
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
