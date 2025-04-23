<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Events\TwoFactorAuthenticationChallenged;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Requests\UserLoginRequest;

class LoginController extends Controller
{
    use ThrottlesLogins;

    public function login(UserLoginRequest $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $user = $this->validateCredentials($request);

        if (! $user) {
            $this->incrementLoginAttempts($request);

            $errorResponse = $request->has('_error_redirect') ? redirect($request->input('_error_redirect')) : back();

            return $errorResponse->withInput()->withErrors(__('Invalid credentials.'));
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('statamic.two-factor-challenge');
        }

        Auth::login($user, $request->boolean('remember'));

        return redirect($request->input('_redirect', '/'))->withSuccess(__('Login successful.'));
    }

    /**
     * Attempt to validate the incoming credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function validateCredentials($request)
    {
        $provider = Auth::getProvider();

        return tap($provider->retrieveByCredentials($request->only($this->username(), 'password')), function ($user) use ($provider, $request) {
            if (! $user || ! $provider->validateCredentials($user, ['password' => $request->password])) {
                $this->fireFailedEvent($request, $user);

                $this->throwFailedAuthenticationException($request);
            }

            if (config('hashing.rehash_on_login', true) && method_exists($provider, 'rehashPasswordIfRequired')) {
                $provider->rehashPasswordIfRequired($user, ['password' => $request->password]);
            }
        });
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @return void
     */
    protected function fireFailedEvent($request, $user = null)
    {
        event(new Failed(Auth::getName(), $user, [
            $this->username() => $request->{$this->username()},
            'password' => $request->password,
        ]));
    }

    public function logout()
    {
        Auth::logout();

        return redirect(request()->get('redirect', '/'));
    }

    protected function username()
    {
        return 'email';
    }
}
