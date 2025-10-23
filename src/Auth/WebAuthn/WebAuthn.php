<?php

namespace Statamic\Auth\WebAuthn;

use Exception;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Contracts\Auth\User;
use Statamic\Facades\User as UserFacade;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

class WebAuthn
{
    public function __construct(
        private AuthenticatorAssertionResponseValidator $responseValidator,
        private Serializer $serializer
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

        $publicKeyCredentialSource = $this->responseValidator->check(
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
}
