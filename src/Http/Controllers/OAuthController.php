<?php

namespace Statamic\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Statamic\Facades\OAuth;

class OAuthController
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            return $this->redirectToProvider($provider);
        }

        $user = OAuth::provider($provider)->findOrCreateUser($providerUser);

        session()->put('oauth-provider', $provider);

        Auth::login($user, config('statamic.oauth.remember_me', true));

        return redirect()->to($this->successRedirectUrl());
    }

    protected function successRedirectUrl()
    {
        $default = cp_route('index');

        $previous = session('_previous.url');

        if (! $query = array_get(parse_url($previous), 'query')) {
            return $default;
        }

        parse_str($query, $query);

        $referer = array_get($query, 'redirect', $default);

        // Only use the referer, if it's a url to the control panel.
        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : $default;
    }
}
