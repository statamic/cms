<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Statamic\Auth\User;
use Statamic\Events\TwoFactorAuthenticationEnabled;

class EnableTwoFactorAuthentication
{
    public function __construct(private TwoFactorAuthenticationProvider $provider)
    {
    }

    public function __invoke(User $user)
    {
        $user
            ->set('two_factor_secret', encrypt($this->provider->generateSecretKey()))
            ->set('two_factor_recovery_codes', encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())))
            ->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
