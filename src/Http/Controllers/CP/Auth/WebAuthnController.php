<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class WebAuthnController
{
    public function createOptions()
    {
        if (! $user = User::current()) {
            throw new Exception('You must be logged in');
        }

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->email(),
            $user->id(),
            $user->name()
        );

        return PublicKeyCredentialCreationOptions::create(
            $this->rpEntity(),
            $userEntity,
            random_bytes(16),
        );
    }

    public function create(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticator-registration#creation-response

        $publicKeyCredential = (new PublicKeyCredentialLoader())->load($request->getContent());

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        try {
            $publicKeyCredentialSource = (new AuthenticatorAttestationResponseValidator)->check(
                $publicKeyCredential->response,
                $this->createOptions(),
                config('statamic.webauthn.rrp_entity.id', config('app.url'))
            );
        } catch (Exception $e) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        if (! $user = User::current()) {
            throw new Exception('You must be logged in');
        }

        WebAuthnRepository::create([
            'id' => $publicKeyCredentialSource->rawId, // should we store it all?
            'user' => $user
        ]);
    }

    public function verifyOptions()
    {
        return PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    public function verify(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticate-your-users#response-verification

        $publicKeyCredential = (new PublicKeyCredentialLoader())->load($request->getContent());

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        // get from passkey repository
        $passkey = WebAuthnRepository::find($publicKeyCredential->rawId);

        if (! $passkey) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        try {
            $publicKeyCredentialSource = (new AuthenticatorAssertionResponseValidator)->check(
                $passkey,
                $publicKeyCredential->response,
                $this->verifyOptions(),
                config('statamic.webauthn.rrp_entity.id', config('app.url'))
            );
        } catch (Exception $e) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        Auth::login($passkey->user, config('statamic.webauthn.remember_me', true));

        return redirect()->to($this->successRedirectUrl());
    }

    public function view()
    {
        throw new Exception('Show a list of the users existing passkeys, with revoke option and the ability to let them create a new one');
    }

    private function rpEntity(): PublicKeyCredentialRpEntity
    {
        return PublicKeyCredentialRpEntity::create(
            name: config('statamic.webauthn.rrp_entity.name', config('app.name')),
            id: config('statamic.webauthn.rrp_entity.id', config('app.url')),
        );
    }

    private function successRedirectUrl()
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
