<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Statamic\Facades\OAuth;
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

        $user = $oauth->findOrCreateUser($providerUser);

        session()->put('oauth-provider', $provider);

        Auth::guard($request->session()->get('statamic.oauth.guard'))
            ->login($user, config('statamic.oauth.remember_me', true));

        return redirect()->to($this->successRedirectUrl());
    }

    protected function successRedirectUrl()
    {
        $default = '/';

        $previous = session('_previous.url');

        if (! $query = array_get(parse_url($previous), 'query')) {
            return $default;
        }

        parse_str($query, $query);

        return array_get($query, 'redirect', $default);
    }
}
