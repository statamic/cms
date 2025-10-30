<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Facades\OAuth;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Concerns\HandlesLogins;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\OAuth\Provider;
use Statamic\Statamic;
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

    public function showLoginForm(Request $request)
    {
        $oauthEnabled = OAuth::enabled();
        $emailLoginEnabled = $oauthEnabled ? config('statamic.oauth.email_login_enabled') : true;

        return Inertia::render('auth/Login', [
            'title' => __('Log in'),
            'oauthEnabled' => $oauthEnabled,
            'emailLoginEnabled' => $emailLoginEnabled,
            'providers' => $oauthEnabled ? $this->oauthProviders() : [],
            'referer' => $this->getReferrer($request),
            'forgotPasswordUrl' => cp_route('password.request'),
            'submitUrl' => cp_route('login'),
            'passkeyOptionsUrl' => cp_route('passkeys.auth.options'),
            'passkeyVerifyUrl' => cp_route('passkeys.auth'),
        ]);
    }

    private function oauthProviders()
    {
        $redirect = parse_url(cp_route('index'))['path'];

        return OAuth::providers()->map(fn (Provider $provider) => [
            'name' => $provider->name(),
            'icon' => Statamic::svg('oauth/'.$provider->name()),
            'url' => $provider->loginUrl().'?redirect='.$redirect,
        ])->values();
    }

    public function login(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);

        $this->checkPasskeyEnforcement($request);

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

    private function checkPasskeyEnforcement(Request $request)
    {
        if (! config('statamic.webauthn.allow_password_login_with_passkey', true)) {
            if ($user = User::findByEmail($request->get($this->username()))) {
                if ($user->passkeys()->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        $this->username() => [trans('statamic::messages.password_passkeys_only')],
                    ]);
                }
            }
        }
    }
}
