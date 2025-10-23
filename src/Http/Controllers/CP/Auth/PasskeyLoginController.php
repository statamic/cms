<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\WebAuthn;
use Statamic\Support\Str;

class PasskeyLoginController
{
    public function options()
    {
        $options = WebAuthn::prepareAssertion();

        return app(Serializer::class)->normalize($options);
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['id', 'rawId', 'response', 'type']);

        $user = WebAuthn::getUserFromCredentials($credentials);

        WebAuthn::validateAssertion($user, $credentials);

        $this->authenticate($user);

        return ['redirect' => $this->successRedirectUrl()];
    }

    private function authenticate(UserContract $user): void
    {
        Auth::login($user, config('statamic.webauthn.remember_me', true));

        session()->elevate();
        session()->regenerate();
    }

    private function successRedirectUrl()
    {
        $referer = request('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : cp_route('index');
    }
}
