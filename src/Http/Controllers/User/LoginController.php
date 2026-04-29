<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\TwoFactor;
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

        $this->checkPasskeyEnforcement($request);

        $user = User::fromUser($this->validateCredentials($request));

        if (TwoFactor::enabled() && $user->hasEnabledTwoFactorAuthentication()) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        // An explicit form redirect overrides any URL stashed by the auth middleware.
        if (($redirect = $request->input('_redirect')) && ! URL::isExternalToApplication($redirect)) {
            redirect()->setIntendedUrl($redirect);
        }

        $this->authenticate($request, $user);

        // Preserve the intended URL for the setup flow to consume after the user completes setup.
        if (TwoFactor::enabled() && $user->isTwoFactorAuthenticationRequired() && ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect(redirect()->getIntendedUrl() ?? route('statamic.site'))
                ->withSuccess(__('Login successful.'));
        }

        return redirect()->intended(route('statamic.site'))->withSuccess(__('Login successful.'));
    }

    private function checkPasskeyEnforcement(Request $request)
    {
        if (config('statamic.webauthn.allow_password_login_with_passkey', true)) {
            return;
        }

        if (! $user = User::findByEmail($request->get($this->username()))) {
            return;
        }

        if ($user->passkeys()->isEmpty()) {
            return;
        }

        $errorRedirect = $request->input('_error_redirect');

        $errorResponse = $errorRedirect && ! URL::isExternalToApplication($errorRedirect)
            ? redirect($errorRedirect)
            : back();

        throw new HttpResponseException(
            $errorResponse->withInput()->withErrors(__('statamic::messages.password_passkeys_only'))
        );
    }

    protected function twoFactorChallengeRedirect(): string
    {
        return config('statamic.users.two_factor_challenge_url') ?? route('statamic.two-factor-challenge');
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

        $redirect = request()->get('redirect');

        $url = $redirect && ! URL::isExternalToApplication($redirect)
            ? $redirect
            : route('statamic.site');

        return redirect($url);
    }

    protected function username()
    {
        return 'email';
    }
}
