<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Statamic\Events\TwoFactorAuthenticationFailed;
use Statamic\Events\ValidTwoFactorAuthenticationCodeProvided;
use Statamic\Http\Controllers\TwoFactorChallengeController as Controller;
use Statamic\Http\Middleware\CP\HandleInertiaRequests;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Http\Requests\TwoFactorChallengeRequest;
use Statamic\Support\Str;

class TwoFactorChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:two-factor');
        $this->middleware(HandleInertiaRequests::class)->except('store');
        $this->middleware(RedirectIfAuthorized::class);
    }

    public function store(TwoFactorChallengeRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceTwoFactorRecoveryCode($code);
        } elseif (! $request->hasValidCode()) {
            TwoFactorAuthenticationFailed::dispatch($user);

            return $this->sendFailedResponse($request);
        }

        ValidTwoFactorAuthenticationCodeProvided::dispatch($user);

        Auth::guard()->login($user, $request->remember());

        $this->clearTwoFactorSession($request);

        $request->session()->elevate();

        $request->session()->regenerate();

        if ($request->inertia() || $request->expectsJson()) {
            return $request->inertia()
                ? Inertia::location($this->redirectPath($request))
                : response('Authenticated');
        }

        return redirect()->intended($this->redirectPath($request));
    }

    protected function formAction()
    {
        return cp_route('two-factor-challenge');
    }

    protected function redirectPath()
    {
        $cp = cp_route('index');
        $referer = request('referer');
        $referredFromCp = Str::startsWith($referer, $cp) && ! Str::startsWith($referer, $cp.'/auth/');

        return $referredFromCp ? $referer : $cp;
    }

    protected function failedRedirectPath()
    {
        return cp_route('two-factor-challenge');
    }
}
