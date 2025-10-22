<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Statamic\Support\Str;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;

class PasskeyController
{
    public function createOptions($challenge = false)
    {
        if (! $user = User::current()) {
            throw new Exception('You must be logged in');
        }

        $options = $this->publicKeyCredentialCreationOptions($user, $challenge);

        return app(SerializerInterface::class)->normalize($options);
    }

    private function publicKeyCredentialCreationOptions($user, $challenge = false): PublicKeyCredentialCreationOptions
    {
        if (! $challenge) {
            $challenge = random_bytes(16);
            session()->put('webauthn.challenge', $challenge);
        }

        $userEntity = Webauthn\PublicKeyCredentialUserEntity::create(
            $user->email(),
            $user->id(),
            $user->name()
        );

        return PublicKeyCredentialCreationOptions::create(
            app(PublicKeyCredentialRpEntity::class),
            $userEntity,
            $challenge,
        );
    }

    public function create(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticator-registration#creation-response
        $publicKeyCredential = app(SerializerInterface::class)->deserialize(
            json_encode($request->all()),
            Webauthn\PublicKeyCredential::class,
            'json'
        );

        if (! $publicKeyCredential->response instanceof Webauthn\AuthenticatorAttestationResponse) {
            throw new Exception(__('Invalid credentials'));
        }

        $responseValidator = Webauthn\AuthenticatorAttestationResponseValidator::create(
            app(CeremonyStepManagerFactory::class)->creationCeremony()
        );

        $options = $this->publicKeyCredentialCreationOptions(
            User::current(),
            session()->pull('webauthn.challenge')
        );

        $publicKeyCredentialSource = $responseValidator->check(
            $publicKeyCredential->response,
            $options,
            $request->getHost()
        );

        session()->forget('webauthn.challenge');

        if (! $user = User::current()) {
            throw new Exception(__('Invalid user'));
        }

        $passkey = app(Passkey::class)
            ->setUser($user)
            ->setName($request->name)
            ->setCredential($publicKeyCredentialSource);

        $passkey->save();

        return ['verified' => true];
    }

    public function delete(Request $request, $id)
    {
        if (! $user = User::current()) {
            abort(403);
        }

        /* @var Passkey $passkey */
        $passkey = $user->passkeys()->firstWhere(fn ($key) => $key->id() == $id);

        if (! $passkey) {
            abort(403);
        }

        $passkey->delete();

        if ($request->wantsJson()) {
            return new JsonResponse([], 201);
        }

        return redirect()->back();
    }

    public function verifyOptions($challenge = false)
    {
        $options = $this->publicKeyCredentialRequestOptions($challenge);

        return app(SerializerInterface::class)->normalize($options);
    }

    private function publicKeyCredentialRequestOptions($challenge = false): PublicKeyCredentialRequestOptions
    {
        if (! $challenge) {
            $challenge = random_bytes(32);
            session()->put('webauthn.challenge', $challenge);
        }

        return PublicKeyCredentialRequestOptions::create(
            $challenge,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    public function verify(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticate-your-users#response-verification
        $publicKeyCredential = app(SerializerInterface::class)->deserialize(
            $request->getContent(),
            Webauthn\PublicKeyCredential::class,
            'json'
        );

        if (! $publicKeyCredential->response instanceof Webauthn\AuthenticatorAssertionResponse) {
            throw new Exception(__('Invalid credentials'));
        }

        $user = User::find($publicKeyCredential->response->userHandle);

        // get from passkey repository
        /* @var Passkey $passkey */
        if (! $passkey = $user->passkeys()->firstWhere(fn (Passkey $key) => $key->id() == $publicKeyCredential->rawId)) {
            throw new Exception(__('No matching passkey found'));
        }

        $responseValidator = Webauthn\AuthenticatorAssertionResponseValidator::create(
            app(CeremonyStepManagerFactory::class)->requestCeremony()
        );

        $options = $this->publicKeyCredentialRequestOptions(
            session()->pull('webauthn.challenge')
        );

        $publicKeyCredentialSource = $responseValidator->check(
            $passkey->credential(),
            $publicKeyCredential->response,
            $options,
            $request->getHost(),
            $user->id()
        );

        // update passkey with latest data
        $passkey
            ->setCredential($publicKeyCredentialSource)
            ->setLastLogin(now());
        $passkey->save();

        Auth::login($passkey->user(), config('statamic.webauthn.remember_me', true));
        session()->elevate();
        session()->regenerate();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => $this->successRedirectUrl(),
            ], 200);
        }

        return redirect()->to($this->successRedirectUrl());
    }

    public function view()
    {
        return Inertia::render('users/Passkeys', [
            'passkeys' => User::current()->passkeys()->map(function (Passkey $passkey) {
                return [
                    'name' => $passkey->name(),
                    'last_login' => ($login = $passkey->lastLogin()) ? $login->toAtomString() : null,
                ];
            }),
            'optionsUrl' => cp_route('passkeys.create-options'),
            'createUrl' => cp_route('passkeys.create'),
            'deleteUrl' => substr(cp_route('passkeys.delete', ['id' => 0]), 0, -1),
        ]);
    }

    private function successRedirectUrl()
    {
        $referer = request('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : cp_route('index');
    }
}
