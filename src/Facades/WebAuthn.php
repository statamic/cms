<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Contracts\Auth\User;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @method static PublicKeyCredentialRequestOptions prepareAssertion()
 * @method static bool validateAssertion(User $user, array $credentials)
 * @method static User getUserFromCredentials(array $credentials)
 * @method static PublicKeyCredentialCreationOptions prepareAttestation(User $user)
 * @method static Passkey validateAttestation(User $user, array $credentials, string $name)
 *
 * @see \Statamic\Auth\WebAuthn\WebAuthn
 */
class WebAuthn extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Auth\WebAuthn\WebAuthn::class;
    }
}
