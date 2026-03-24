<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Concerns\HandlesLogins;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Requests\UserLoginRequest;

class LoginController extends Controller
{
    use HandlesLogins;

    public function login(UserLoginRequest $request)
    {
        $this->handleTooManyLoginAttempts($request);

        $user = User::fromUser($this->validateCredentials($request));

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        $this->authenticate($request, $user);

        $redirect = $request->input('_redirect', '/');

        return redirect(URL::isExternalToApplication($redirect) ? '/' : $redirect)->withSuccess(__('Login successful.'));
    }

    protected function twoFactorChallengeRedirect(): string
    {
        return route('statamic.two-factor-challenge');
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request)
    {
        $errorRedirect = $request->input('_error_redirect');

        $errorResponse = $errorRedirect && ! URL::isExternalToApplication($errorRedirect)
            ? redirect($errorRedirect)
            : back();

        throw new HttpResponseException($errorResponse->withInput()->withErrors(__('Invalid credentials.')));
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

        $redirect = request()->get('redirect', '/');

        return redirect(URL::isExternalToApplication($redirect) ? '/' : $redirect);
    }

    protected function username()
    {
        return 'email';
    }
}
