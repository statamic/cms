<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Statamic\Events\TwoFactorAuthenticationChallenged;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Exceptions\OAuthEmailExistsException;
use Statamic\Facades\OAuth;
use Statamic\Facades\TwoFactor;
use Statamic\Facades\URL;
use Statamic\Support\Str;

use function Statamic\trans as __;

class OAuthController
{
    public function redirectToProvider(Request $request, string $provider)
    {
        $referer = $request->headers->get('referer') ?? '';
        $guard = config('statamic.users.guards.web', 'web');

        if (! OAuth::providers()->has($provider)) {
            throw new NotFoundHttpException();
        }

        $isCp = Str::startsWith(parse_url($referer)['path'], Str::ensureLeft(config('statamic.cp.route'), '/'));

        if ($isCp) {
            $guard = config('statamic.users.guards.cp', 'web');
        }

        if (Auth::guard($guard)->check() && $response = $this->requireElevatedSession($request, $isCp)) {
            return $response;
        }

        $request->session()->put('statamic.oauth.guard', $guard);
        $request->session()->put('statamic.oauth.redirect', $request->query('redirect'));

        return Socialite::driver($provider)->redirect();
    }

    private function requireElevatedSession(Request $request, bool $isCp)
    {
        if (! config('statamic.users.elevated_sessions_enabled') || $request->hasElevatedSession()) {
            return null;
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => __('Requires an elevated session.')], 403);
        }

        $challenge = $isCp ? cp_route('confirm-password') : route('statamic.elevated-session');

        return redirect()->setIntendedUrl($request->fullUrl())->to($challenge);
    }

    public function handleProviderCallback(Request $request, string $provider)
    {
        $oauth = OAuth::provider($provider);

        if (! $oauth) {
            throw new NotFoundHttpException();
        }

        $guard = $request->session()->get('statamic.oauth.guard');

        // When already authenticated, the callback is a request to connect a provider to the current
        // account rather than to sign in. We'll enforce an elevated session *before* the session
        // state gets consumed so the connection can succeed after they complete re-elevation.
        if (Auth::guard($guard)->check() && $response = $this->requireElevatedSession($request, $this->isCpRequest())) {
            return $response;
        }

        try {
            $providerUser = $oauth->getSocialiteUser();
        } catch (InvalidStateException $e) {
            return $this->redirectToProvider($request, $provider);
        }

        if (Auth::guard($guard)->check()) {
            return $this->connectProvider($oauth, $providerUser, Auth::guard($guard)->user());
        }

        if ($user = $oauth->findUser($providerUser)) {
            if (config('statamic.oauth.merge_user_data', true)) {
                $user = $oauth->mergeUser($user, $providerUser);
            }
        } elseif (config('statamic.oauth.create_user', true)) {
            try {
                $user = $oauth->createUser($providerUser);
            } catch (OAuthEmailExistsException $e) {
                return redirect()
                    ->to($this->unauthorizedRedirectUrl())
                    ->with('error', __('statamic::messages.oauth_email_exists'));
            }
        }

        if ($user) {
            session()->put('oauth-provider', $provider);

            // OAuth must not bypass two-factor. When the user has it enabled,
            // defer the login and send them through the challenge instead.
            if (TwoFactor::enabled() && $user->hasEnabledTwoFactorAuthentication()) {
                return $this->twoFactorChallenge($user);
            }

            Auth::guard($guard)->login($user, config('statamic.oauth.remember_me', true));

            session()->elevate();

            return redirect()->to($this->successRedirectUrl());
        }

        return redirect()->to($this->unauthorizedRedirectUrl());
    }

    public function disconnect(Request $request, string $provider)
    {
        $oauth = OAuth::provider($provider);

        if (! $oauth) {
            throw new NotFoundHttpException();
        }

        $oauth->forgetUser($request->user());

        if ($request->wantsJson()) {
            return new JsonResponse([], 204);
        }

        return back()->with('success', __('statamic::messages.oauth_disconnected', ['provider' => $oauth->label()]));
    }

    protected function connectProvider($oauth, $providerUser, $user)
    {
        // Connecting relies on the stateful "state" parameter to protect against
        // forced-connection CSRF, so it cannot be done with a stateless provider.
        if ($oauth->isStateless()) {
            return redirect()
                ->to($this->successRedirectUrl())
                ->with('error', __('statamic::messages.oauth_connect_unsupported'));
        }

        $existingUserId = $oauth->getUserId($providerUser->getId());

        if ($existingUserId === $user->getAuthIdentifier()) {
            return redirect()
                ->to($this->successRedirectUrl())
                ->with('success', __('statamic::messages.oauth_already_connected', ['provider' => $oauth->label()]));
        }

        if ($existingUserId) {
            return redirect()
                ->to($this->successRedirectUrl())
                ->with('error', __('statamic::messages.oauth_belongs_to_another_user', ['provider' => $oauth->label()]));
        }

        $oauth->setUserProviderId($user, $providerUser->getId());

        return redirect()
            ->to($this->successRedirectUrl())
            ->with('success', __('statamic::messages.oauth_connected', ['provider' => $oauth->label()]));
    }

    protected function successRedirectUrl()
    {
        $redirect = session('statamic.oauth.redirect');

        if (! $redirect || URL::isExternalToApplication($redirect)) {
            return '/';
        }

        return $redirect;
    }

    protected function unauthorizedRedirectUrl()
    {
        // If a URL has been explicitly defined, use that.
        if ($url = config('statamic.oauth.unauthorized_redirect')) {
            return $url;
        }

        // We'll check the redirect to see if they were intending on
        // accessing the CP. If they were, we'll redirect them to
        // the unauthorized page in the CP. Otherwise, to home.

        return $this->isCpRequest() ? cp_route('unauthorized') : '/';
    }

    protected function twoFactorChallenge($user)
    {
        session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => config('statamic.oauth.remember_me', true),
        ]);

        // Preserve the OAuth destination so the challenge sends the user
        // there once they've passed two-factor.
        redirect()->setIntendedUrl($this->successRedirectUrl());

        TwoFactorAuthenticationChallenged::dispatch($user);

        return redirect($this->twoFactorChallengeUrl());
    }

    protected function twoFactorChallengeUrl()
    {
        return $this->isCpRequest()
            ? cp_route('two-factor-challenge')
            : config('statamic.users.two_factor_challenge_url') ?? route('statamic.two-factor-challenge');
    }

    protected function isCpRequest()
    {
        $redirect = (string) session('statamic.oauth.redirect');
        $cp = '/'.config('statamic.cp.route');

        return $redirect === $cp || Str::startsWith($redirect, $cp.'/');
    }
}
