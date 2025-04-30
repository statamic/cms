<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Statamic\Facades\OAuth;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Concerns\HandlesLogins;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Support\Str;

use function Statamic\trans as __;

class LoginController extends CpController
{
    use HandlesLogins;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(RedirectIfAuthorized::class)->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        $data = [
            'title' => __('Log in'),
            'oauth' => $enabled = OAuth::enabled(),
            'emailLoginEnabled' => $enabled ? config('statamic.oauth.email_login_enabled') : true,
            'providers' => $enabled ? OAuth::providers() : [],
            'referer' => $this->getReferrer($request),
            'hasError' => $this->hasError(),
        ];

        $view = view('statamic::auth.login', $data);

        if ($request->expired) {
            return $view->withErrors(__('Session Expired'));
        }

        return $view;
    }

    public function login(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);

        $this->handleTooManyLoginAttempts($request);

        $user = User::fromUser($this->validateCredentials($request));

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        $this->authenticate($request, $user);

        return $this->authenticated($request, $this->guard()->user());
    }

    protected function twoFactorChallengeRedirect(): string
    {
        return cp_route('two-factor-challenge');
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
        event(new Failed($this->guard()?->name, $user, [
            $this->username() => $request->{$this->username()},
            'password' => $request->password,
        ]));
    }

    public function redirectPath()
    {
        $cp = cp_route('index');
        $referer = request('referer');
        $referredFromCp = Str::startsWith($referer, $cp) && ! Str::startsWith($referer, $cp.'/auth/');

        return $referredFromCp ? $referer : $cp;
    }

    protected function authenticated(Request $request, $user)
    {
        return $request->expectsJson()
            ? response('Authenticated')
            : redirect()->intended($this->redirectPath());
    }

    protected function credentials(Request $request)
    {
        $credentials = [
            $this->username() => strtolower($request->get($this->username())),
            'password' => $request->get('password'),
        ];

        return $credentials;
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect($request->redirect ?? '/');
    }

    protected function getReferrer()
    {
        $referrer = url()->previous();

        return $referrer === cp_route('unauthorized') ? cp_route('index') : $referrer;
    }

    public function username()
    {
        return 'email';
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
