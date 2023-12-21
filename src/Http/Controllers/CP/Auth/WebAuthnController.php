<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\RSA\RS256;use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;
use Webauthn;

class WebAuthnController
{
    public function createOptions()
    {
        if (! $user = User::current()) {
            throw new Exception('You must be logged in');
        }

        if (! $challenge = session()->pull('webauthn.challenge')) {
            $challenge = random_bytes(16);
            session()->put('webauthn.challenge', $challenge);
        }

        $userEntity = Webauthn\PublicKeyCredentialUserEntity::create(
            $user->email(),
            $user->id(),
            $user->name()
        );

        return Webauthn\PublicKeyCredentialCreationOptions::create(
            $this->rpEntity(),
            $userEntity,
            $challenge,
        );
    }

    public function create(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticator-registration#creation-response
        $publicKeyCredential = $this->credentialLoader()->loadArray($request->all());

        if (! $publicKeyCredential->response instanceof Webauthn\AuthenticatorAttestationResponse) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        try {
            $responseValidator = Webauthn\AuthenticatorAttestationResponseValidator::create(
                $this->attestationSupportManager(),
                null,
                null,
                Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler::create()
            );

            $publicKeyCredentialSource = $responseValidator->check(
                $publicKeyCredential->response,
                $this->createOptions(),
                $request->getHost(),
                config('statamic.webauthn.rrp_entity.id', [])
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
        return Webauthn\PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            userVerification: Webauthn\PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    public function verify(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticate-your-users#response-verification

        $publicKeyCredential = $this->credentialLoader()->load($request->getContent());

        if (! $publicKeyCredential->response instanceof Webauthn\AuthenticatorAssertionResponse) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        // get from passkey repository
        $passkey = WebAuthnRepository::find($publicKeyCredential->rawId);

        if (! $passkey) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        try {
            $algorithmManager = Manager::create()
                ->add(
                    ES256::create(),
                    RS256::create()
                )
            ;

            $responseValidator = Webauthn\AuthenticatorAssertionResponseValidator::create(
                null,
                null,
                Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler::create(),
                $algorithmManager
            );

            $publicKeyCredentialSource = $responseValidator->check(
                $passkey,
                $publicKeyCredential->response,
                $this->verifyOptions(),
                config('statamic.webauthn.rrp_entity.id', [])
            );
        } catch (Exception $e) {
            throw new Exception('Invalid credentials'); // maybe redirect back with errors?
        }

        Auth::login($passkey->user, config('statamic.webauthn.remember_me', true));

        return redirect()->to($this->successRedirectUrl());
    }

    public function view()
    {
        return view('statamic::users.webauthn', [
            'passkeys' => [],
        ]);
    }

    private function attestationSupportManager(): Webauthn\AttestationStatement\AttestationStatementSupportManager
    {
        $attestationStatementSupportManager = Webauthn\AttestationStatement\AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(Webauthn\AttestationStatement\NoneAttestationStatementSupport::create());

        return $attestationStatementSupportManager;
    }

    private function credentialLoader(): Webauthn\PublicKeyCredentialLoader
    {
        $attestationObjectLoader = Webauthn\AttestationStatement\AttestationObjectLoader::create($this->attestationSupportManager());

        return Webauthn\PublicKeyCredentialLoader::create($attestationObjectLoader);
    }

    private function rpEntity(): Webauthn\PublicKeyCredentialRpEntity
    {
        return Webauthn\PublicKeyCredentialRpEntity::create(
            name: config('statamic.webauthn.rrp_entity.name', config('app.name')),
            id: config('statamic.webauthn.rrp_entity.id', null),
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
