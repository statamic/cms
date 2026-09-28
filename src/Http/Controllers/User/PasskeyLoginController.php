<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\URL;
use Statamic\Facades\WebAuthn;
use Statamic\Http\Controllers\Controller;

class PasskeyLoginController extends Controller
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

        return ['redirect' => $this->successRedirectUrl($request)];
    }

    protected function authenticate(UserContract $user): void
    {
        Auth::login($user, config('statamic.webauthn.remember_me', true));

        session()->elevate();
        session()->regenerate();
    }

    protected function successRedirectUrl(Request $request): string
    {
        if (($redirect = $request->input('redirect')) && ! URL::isExternalToApplication($redirect)) {
            return $redirect;
        }

        return $request->session()->pull('url.intended', $this->defaultRedirectUrl());
    }

    protected function defaultRedirectUrl(): string
    {
        return '/';
    }
}
