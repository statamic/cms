<?php

namespace Statamic\Auth\TwoFactor;

use Statamic\Auth\User;

class EnableTwoFactorAuthentication
{
    public function __construct(private TwoFactorAuthenticationProvider $provider)
    {
    }

    public function __invoke(User $user, bool $force = false)
    {
        $user
            ->remove('two_factor_confirmed_at')
            ->remove('two_factor_completed');

        if ($force) {
            $user->set('two_factor_secret', encrypt($this->provider->generateSecretKey()));
            app(GenerateRecoveryCodes::class)($user);
        }

        $user->save();
    }
}
