<?php

namespace Statamic\Auth\TwoFactor;

use Statamic\Auth\User;

class EnableTwoFactorAuthentication
{
    public function __construct(private Google2FA $provider)
    {
    }

    public function __invoke(User $user, bool $resetSecret)
    {
        $user
            ->remove('two_factor_confirmed_at')
            ->remove('two_factor_completed');

        if ($resetSecret) {
            $user->set('two_factor_secret', encrypt($this->provider->generateSecretKey()));
            app(GenerateRecoveryCodes::class)($user);
        }

        $user->save();
    }
}
