<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\RSA\RS256;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\Passkey;
use Statamic\Facades\User;
use Statamic\Support\Str;
use Webauthn;

class WebAuthnController
{
    public function createOptions($challenge = false)
    {
        if (! $user = User::current()) {
            throw new Exception('You must be logged in');
        }

        if (! $challenge) {
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
            throw new Exception(__('Invalid credentials'));
        }

        $responseValidator = Webauthn\AuthenticatorAttestationResponseValidator::create(
            $this->attestationSupportManager(),
            null,
            null,
            Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler::create()
        );

        $publicKeyCredentialSource = $responseValidator->check(
            $publicKeyCredential->response,
            $this->createOptions(session()->pull('webauthn.challenge')),
            $request->getHost(),
            ($rpEntityId = config('statamic.webauthn.rrp_entity.id')) ? [$rpEntityId] : []
        );

        session()->forget('webauthn.challenge');

        if (! $user = User::current()) {
            throw new Exception(__('Invalid user'));
        }

        $passkey = Passkey::make()
            ->id($publicKeyCredential->id)
            ->user($user)
            ->data($publicKeyCredentialSource->jsonSerialize());

        $passkey->save();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'verified' => true,
            ], 200);
        }

        return redirect()->to(route('statamic.cp.webauthn.view'));
    }

    public function delete(Request $request, $id)
    {
        $passkey = Passkey::find($id);

        if (! $passkey || ($passkey->user() != User::current())) {
            abort(403);
        }

        $passkey->delete();

        if ($request->wantsJson()) {
            return new JsonResponse([], 201);
        }

        return redirect()->back();
    }

    public function userOptions(Request $request)
    {
        if (! $user = User::findByEmail($request->email)) {
            return [];
        }

        $passkeys = $user->passkeys();

        if ($passkeys->isEmpty()) {
            return [];
        }

        return config('statamic.webauthn.allow_password_login_with_passkey') ? ['password', 'passkey'] : ['passkey'];
    }

    public function verifyOptions($challenge = false)
    {
        if (! $challenge) {
            $challenge = random_bytes(32);
            session()->put('webauthn.challenge', $challenge);
        }

        return Webauthn\PublicKeyCredentialRequestOptions::create(
            $challenge,
            userVerification: Webauthn\PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    public function verify(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticate-your-users#response-verification

        $publicKeyCredential = $this->credentialLoader()->load($request->getContent());

        if (! $publicKeyCredential->response instanceof Webauthn\AuthenticatorAssertionResponse) {
            throw new Exception(__('Invalid credentials'));
        }

        // get from passkey repository
        $passkey = Passkey::find($publicKeyCredential->id);

        if (! $passkey) {
            throw new Exception(__('No matching passkey found'));
        }

        $algorithmManager = Manager::create()->add(ES256::create(), RS256::create());

        $responseValidator = Webauthn\AuthenticatorAssertionResponseValidator::create(
            null,
            null,
            Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler::create(),
            $algorithmManager
        );

        $publicKeyCredentialSource = $responseValidator->check(
            $passkey->toPublicKeyCredentialSource(),
            $publicKeyCredential->response,
            $this->verifyOptions(session()->pull('webauthn.challenge')),
            $request->getHost(),
            null,
            ($rpEntityId = config('statamic.webauthn.rrp_entity.id')) ? [$rpEntityId] : []
        );

        // update passkey with latest data
        $passkey->data($publicKeyCredentialSource->jsonSerialize());
        $passkey->set('last_login', now()->timestamp);
        $passkey->save();

        Auth::login($passkey->user(), config('statamic.webauthn.remember_me', true));

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => $this->successRedirectUrl(),
            ], 200);
        }

        return redirect()->to($this->successRedirectUrl());
    }

    public function view()
    {
        return view('statamic::users.webauthn', [
            'passkeys' => User::current()->passkeys(),
            'routes' => [
                'options' => route('statamic.cp.webauthn.create-options'),
                'verify' => route('statamic.cp.webauthn.create'),
                'delete' => substr(route('statamic.cp.webauthn.delete', ['id' => 0]), 0, -1),
            ],
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
        $referer = request('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : cp_route('index');
    }
}
