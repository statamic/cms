<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Exceptions\OAuthEmailExistsException;
use Statamic\Facades\OAuth;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class OAuthController
{
    public function redirectToProvider(Request $request, string $provider)
    {
        $referer = $request->headers->get('referer') ?? '';
        $guard = config('statamic.users.guards.web', 'web');

        if (! OAuth::providers()->has($provider)) {
            throw new NotFoundHttpException();
        }

        if (Str::startsWith(parse_url($referer)['path'], Str::ensureLeft(config('statamic.cp.route'), '/'))) {
            $guard = config('statamic.users.guards.cp', 'web');
        }

        $request->session()->put('statamic.oauth.guard', $guard);

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, string $provider)
    {
        $oauth = OAuth::provider($provider);

        if (! $oauth) {
            throw new NotFoundHttpException();
        }

        try {
            $providerUser = $oauth->getSocialiteUser();
        } catch (InvalidStateException $e) {
            return $this->redirectToProvider($request, $provider);
        }

        $guard = $request->session()->get('statamic.oauth.guard');

        // When already authenticated, the callback is a request to link
        // a provider to the current account rather than to sign in.
        if (Auth::guard($guard)->check()) {
            return $this->linkProvider($oauth, $providerUser, Auth::guard($guard)->user());
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

            Auth::guard($guard)->login($user, config('statamic.oauth.remember_me', true));

            session()->elevate();

            return redirect()->to($this->successRedirectUrl());
        }

        return redirect()->to($this->unauthorizedRedirectUrl());
    }

    public function unlink(Request $request, string $provider)
    {
        $oauth = OAuth::provider($provider);

        if (! $oauth) {
            throw new NotFoundHttpException();
        }

        $oauth->forgetUser($request->user());

        if ($request->wantsJson()) {
            return new JsonResponse([], 204);
        }

        return back()->with('success', __('statamic::messages.oauth_unlinked', ['provider' => $oauth->label()]));
    }

    protected function linkProvider($oauth, $providerUser, $user)
    {
        // Linking relies on the stateful "state" parameter to protect against
        // forced-linking CSRF, so it cannot be done with a stateless provider.
        if ($oauth->isStateless()) {
            return redirect()
                ->to($this->successRedirectUrl())
                ->with('error', __('statamic::messages.oauth_link_unsupported'));
        }

        $existingUserId = $oauth->getUserId($providerUser->getId());

        if ($existingUserId === $user->id()) {
            return redirect()
                ->to($this->successRedirectUrl())
                ->with('success', __('statamic::messages.oauth_link_already_connected', ['provider' => $oauth->label()]));
        }

        if ($existingUserId) {
            return redirect()
                ->to($this->successRedirectUrl())
                ->with('error', __('statamic::messages.oauth_link_belongs_to_another_user', ['provider' => $oauth->label()]));
        }

        $oauth->setUserProviderId($user, $providerUser->getId());

        return redirect()
            ->to($this->successRedirectUrl())
            ->with('success', __('statamic::messages.oauth_linked', ['provider' => $oauth->label()]));
    }

    protected function successRedirectUrl()
    {
        $default = '/';

        $previous = session('_previous.url');

        if (! $query = Arr::get(parse_url($previous), 'query')) {
            return $default;
        }

        parse_str($query, $query);

        $redirect = Arr::get($query, 'redirect', $default);

        return URL::isExternalToApplication($redirect) ? $default : $redirect;
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

        $default = '/';
        $previous = session('_previous.url');

        if (! $query = Arr::get(parse_url($previous), 'query')) {
            return $default;
        }

        parse_str($query, $query);

        if (! $redirect = Arr::get($query, 'redirect')) {
            return $default;
        }

        return $redirect === '/'.config('statamic.cp.route') ? cp_route('unauthorized') : $default;
    }
}
