<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Statamic\Events\TwoFactorAuthenticationFailed;
use Statamic\Events\ValidTwoFactorAuthenticationCodeProvided;
use Statamic\Facades\URL;
use Statamic\Http\Middleware\CP\HandleInertiaRequests;
use Statamic\Http\Middleware\RedirectIfAuthenticated;
use Statamic\Http\Requests\TwoFactorChallengeRequest;

class TwoFactorChallengeController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('throttle:two-factor');
        $this->middleware(HandleInertiaRequests::class)->except('store');
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function index(TwoFactorChallengeRequest $request)
    {
        if (! $request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('statamic.cp.login'));
        }

        return Inertia::render('auth/two-factor/Challenge', [
            'action' => $this->formAction(),
            'mode' => session()->get('errors')?->getBag('default')->has('recovery_code') ? 'recovery_code' : 'code',
            'csrfToken' => csrf_token(),
            'redirect' => $request->redirect,
        ]);
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

        $request->session()->forget(['login.id', 'login.remember']);

        $request->session()->elevate();

        $request->session()->regenerate();

        $redirect = $this->redirectPath($request);

        if ($request->inertia() || $request->expectsJson()) {
            return $request->inertia()
                ? Inertia::location($redirect)
                : response('Authenticated');
        }

        return redirect($redirect);
    }

    protected function sendFailedResponse(TwoFactorChallengeRequest $request)
    {
        if ($errorRedirect = $request->input('_error_redirect')) {
            if (! URL::isExternalToApplication($errorRedirect)) {
                return $request->sendFailedTwoFactorChallengeResponse($errorRedirect);
            }
        }

        return $request->sendFailedTwoFactorChallengeResponse($this->failedRedirectPath());
    }

    protected function formAction()
    {
        return route('statamic.two-factor-challenge');
    }

    protected function redirectPath(Request $request)
    {
        $intended = $request->session()->pull('url.intended', $this->defaultRedirectPath());

        if (($redirect = $request->input('_redirect')) && ! URL::isExternalToApplication($redirect)) {
            return $redirect;
        }

        return $intended;
    }

    protected function defaultRedirectPath(): string
    {
        return route('statamic.site');
    }

    protected function failedRedirectPath()
    {
        return config('statamic.users.two_factor_challenge_url') ?? route('statamic.two-factor-challenge');
    }
}
