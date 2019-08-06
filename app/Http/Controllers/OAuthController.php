<?php

namespace Statamic\Http\Controllers;

use Statamic\API\User;
use Statamic\OAuth\Provider;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

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

        $user = $this->findOrCreateUser($provider, $providerUser);

        Auth::login($user, true);

        return redirect()->to($this->successRedirectUrl());
    }

    protected function findOrCreateUser($provider, $providerUser)
    {
        if ($user = User::findByOAuthId($provider, $providerUser->getId())) {
            return $user;
        }

        return (new Provider($provider))->createUser($providerUser);
    }

    protected function successRedirectUrl()
    {
        //
    }
}