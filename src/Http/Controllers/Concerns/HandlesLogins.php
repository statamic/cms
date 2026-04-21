<?php

namespace Statamic\Http\Controllers\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Contracts\Auth\User;
use Statamic\Events\TwoFactorAuthenticationChallenged;
use Statamic\Facades\URL;

trait HandlesLogins
{
    use ThrottlesLogins;

    protected function handleTooManyLoginAttempts(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
    }

    protected function validateCredentials($request)
    {
        $provider = $this->guard()->getProvider();

        return tap($provider->retrieveByCredentials($request->only($this->username(), 'password')), function ($user) use ($provider, $request) {
            if (! $user || ! $provider->validateCredentials($user, ['password' => $request->password])) {
                $this->failAuthentication($request, $user);
            }

            if (config('hashing.rehash_on_login', true) && method_exists($provider, 'rehashPasswordIfRequired')) {
                $provider->rehashPasswordIfRequired($user, ['password' => $request->password]);
            }
        });
    }

    protected function guard()
    {
        return Auth::guard();
    }

    protected function failAuthentication(Request $request, ?Authenticatable $user = null)
    {
        $this->fireFailedEvent($request, $user);
        $this->incrementLoginAttempts($request);
        $this->throwFailedAuthenticationException($request);
    }

    protected function throwFailedAuthenticationException(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function twoFactorChallengeResponse(Request $request, User $user)
    {
        $session = [
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ];

        if ($challengeUrl = $request->input('_two_factor_challenge_url')) {
            if (! URL::isExternalToApplication($challengeUrl)) {
                $session['login.two_factor_challenge_url'] = $challengeUrl;
            }
        }

        if ($setupUrl = $this->decryptSetupUrl($request->input('_two_factor_setup_url'))) {
            $session['login.two_factor_setup_url'] = $setupUrl;
        }

        if ($redirect = $request->input('_redirect')) {
            if (! URL::isExternalToApplication($redirect)) {
                $session['login.redirect'] = $redirect;
            }
        }

        $request->session()->put($session);

        TwoFactorAuthenticationChallenged::dispatch($user);

        $challengeRedirect = ($challengeUrl = $request->input('_two_factor_challenge_url'))
            && ! URL::isExternalToApplication($challengeUrl)
            ? $challengeUrl
            : $this->twoFactorChallengeRedirect();

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect($challengeRedirect);
    }

    abstract protected function twoFactorChallengeRedirect(): string;

    protected function authenticate(Request $request, Authenticatable $user): void
    {
        $this->guard()->login($user, $request->boolean('remember'));

        $request->session()->elevate();

        $request->session()->regenerate();
    }

    private function decryptSetupUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            $url = decrypt($value);
        } catch (DecryptException $e) {
            return null;
        }

        return URL::isExternalToApplication($url) ? null : $url;
    }
}
