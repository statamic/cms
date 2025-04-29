<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Statamic\Facades\OAuth;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class OAuthController
{
    public function redirectToProvider(Request $request, string $provider)
    {
        $referer = $request->headers->get('referer');
        $guard = config('statamic.users.guards.web', 'web');

        if (Str::startsWith(parse_url($referer)['path'], Str::ensureLeft(config('statamic.cp.route'), '/'))) {
            $guard = config('statamic.users.guards.cp', 'web');
        }

        $request->session()->put('statamic.oauth.guard', $guard);

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, string $provider)
    {
        $oauth = OAuth::provider($provider);

        try {
            $providerUser = $oauth->getSocialiteUser();
        } catch (InvalidStateException $e) {
            return $this->redirectToProvider($request, $provider);
        }

        if ($user = $oauth->findUser($providerUser)) {
            if (config('statamic.oauth.merge_user_data', true)) {
                $user = $oauth->mergeUser($user, $providerUser);
            }
        } elseif (config('statamic.oauth.create_user', true)) {
            $user = $oauth->createUser($providerUser);
        }

        if ($user) {
            session()->put('oauth-provider', $provider);

            Auth::guard($request->session()->get('statamic.oauth.guard'))
                ->login($user, config('statamic.oauth.remember_me', true));

            session()->elevate();

            return redirect()->to($this->successRedirectUrl());
        }

        return redirect()->to($this->unauthorizedRedirectUrl());
    }

    protected function successRedirectUrl()
    {
        $default = '/';

        $previous = session('_previous.url');

        if (! $query = Arr::get(parse_url($previous), 'query')) {
            return $default;
        }

        parse_str($query, $query);

        return Arr::get($query, 'redirect', $default);
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
