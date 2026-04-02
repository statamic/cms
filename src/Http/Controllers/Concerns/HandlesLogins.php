<?php

namespace Statamic\Http\Controllers\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Contracts\Auth\User;
use Statamic\Events\TwoFactorAuthenticationChallenged;

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
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect($this->twoFactorChallengeRedirect());
    }

    abstract protected function twoFactorChallengeRedirect(): string;

    protected function authenticate(Request $request, Authenticatable $user): void
    {
        $this->guard()->login($user, $request->boolean('remember'));

        $request->session()->elevate();

        $request->session()->regenerate();
    }
}
