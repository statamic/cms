<?php

namespace Statamic\Auth\TwoFactor;

use Statamic\Auth\User;

class DisableTwoFactorAuthentication
{
    public function __invoke(User $user)
    {
        $user
            ->remove('two_factor_confirmed_at')
            ->remove('two_factor_completed')
            ->remove('two_factor_recovery_codes')
            ->remove('two_factor_secret')
            ->clearLastTwoFactorChallenged()
            ->save();
    }
}
