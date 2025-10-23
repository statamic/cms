<?php

namespace Statamic\Auth\WebAuthn;

use Exception;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Contracts\Auth\User;
use Statamic\Facades\User as UserFacade;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class WebAuthn
{
    public function __construct(
        private AuthenticatorAssertionResponseValidator $assertionResponseValidator,
        private AuthenticatorAttestationResponseValidator $attestationResponseValidator,
        private Serializer $serializer,
        private PublicKeyCredentialRpEntity $rpEntity
    ) {
        //
    }

    public function prepareAssertion(): PublicKeyCredentialRequestOptions
    {
        $challenge = random_bytes(32);

        session()->put('webauthn.challenge', $challenge);

        return $this->getRequestOptions($challenge);
    }

    public function validateAssertion(User $user, array $credentials): bool
    {
        $publicKeyCredential = $this->getPublicKeyCredential($credentials);

        $passkey = $this->getPasskey($user, $publicKeyCredential);

        $options = $this->getRequestOptions(session()->pull('webauthn.challenge'));

        $publicKeyCredentialSource = $this->assertionResponseValidator->check(
            $passkey->credential(),
            $publicKeyCredential->response,
            $options,
            request()->getHost(),
            $user->id()
        );

        $passkey
            ->setCredential($publicKeyCredentialSource)
            ->setLastLogin(now())
            ->save();

        return true;
    }

    public function getUserFromCredentials(array $credentials): User
    {
        $publicKey = $this->getPublicKeyCredential($credentials);

        if (! $publicKey->response instanceof AuthenticatorAssertionResponse) {
            throw new Exception(__('Invalid credentials'));
        }

        if (! $user = UserFacade::find($publicKey->response->userHandle)) {
            throw new Exception(__('User not found'));
        }

        return $user;
    }

    public function prepareAttestation(User $user): PublicKeyCredentialCreationOptions
    {
        $challenge = random_bytes(16);

        session()->put('webauthn.challenge', $challenge);

        return $this->getCreationOptions($user, $challenge);
    }

    public function validateAttestation(User $user, array $credentials, string $name): Passkey
    {
        $publicKeyCredential = $this->getPublicKeyCredential($credentials);

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new Exception(__('Invalid credentials'));
        }

        $options = $this->getCreationOptions($user, session()->pull('webauthn.challenge'));

        $publicKeyCredentialSource = $this->attestationResponseValidator->check(
            $publicKeyCredential->response,
            $options,
            request()->getHost()
        );

        $passkey = app(Passkey::class)
            ->setUser($user)
            ->setName($name)
            ->setCredential($publicKeyCredentialSource);

        $passkey->save();

        return $passkey;
    }

    private function getRequestOptions(?string $challenge): PublicKeyCredentialRequestOptions
    {
        return PublicKeyCredentialRequestOptions::create(
            $challenge,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    private function getPasskey(User $user, PublicKeyCredential $credential): Passkey
    {
        $passkey = $user->passkeys()->first(
            fn (Passkey $passkey) => $passkey->credential()->publicKeyCredentialId === $credential->rawId
        );

        if (! $passkey) {
            throw new Exception(__('No matching passkey found'));
        }

        return $passkey;
    }

    private function getPublicKeyCredential(array $credentials): PublicKeyCredential
    {
        return $this->serializer->deserialize(
            json_encode($credentials),
            PublicKeyCredential::class,
            'json'
        );
    }

    private function getCreationOptions(User $user, string $challenge): PublicKeyCredentialCreationOptions
    {
        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->email(),
            $user->id(),
            $user->name()
        );

        return PublicKeyCredentialCreationOptions::create(
            $this->rpEntity,
            $userEntity,
            $challenge
        );
    }
}
