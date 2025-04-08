<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Facades\OAuth;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Support\Str;

class LoginController extends CpController
{
    use ThrottlesLogins;

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

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return $this->authenticated($request, $this->guard()->user());
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function guard()
    {
        return Auth::guard();
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
        // if the user has been locked, show the locked view
        if (User::current()->two_factor_locked) {
            return redirect(cp_route('two-factor.locked'));
        }

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
        // remove the last challenged
        TwoFactorUser::clearLastChallenged();

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
