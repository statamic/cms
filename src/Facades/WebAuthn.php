<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\User;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @method static PublicKeyCredentialRequestOptions prepareAssertion()
 * @method static bool validateAssertion(User $user, array $credentials)
 * @method static User getUserFromCredentials(array $credentials)
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
