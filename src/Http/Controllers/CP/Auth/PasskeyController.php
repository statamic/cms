<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Webauthn;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;

class PasskeyController
{
    public function create()
    {
        if (! $user = User::current()) {
            throw new Exception('You must be logged in');
        }

        $options = $this->publicKeyCredentialCreationOptions($user);

        return app(Serializer::class)->normalize($options);
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

    public function store(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticator-registration#creation-response
        $publicKeyCredential = app(Serializer::class)->deserialize(
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

    public function destroy(Request $request, $id)
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

    public function index()
    {
        return Inertia::render('users/Passkeys', [
            'passkeys' => User::current()->passkeys()->map(function (Passkey $passkey) {
                return [
                    'name' => $passkey->name(),
                    'last_login' => ($login = $passkey->lastLogin()) ? $login->toAtomString() : null,
                ];
            }),
            'createUrl' => cp_route('passkeys.create'),
            'storeUrl' => cp_route('passkeys.store'),
            'deleteUrl' => substr(cp_route('passkeys.destroy', ['id' => 0]), 0, -1),
        ]);
    }
}
