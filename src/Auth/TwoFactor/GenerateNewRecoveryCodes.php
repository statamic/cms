<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Statamic\Auth\User;

class GenerateNewRecoveryCodes
{
    public function __invoke(User $user)
    {
        $user
            ->set('two_factor_recovery_codes', encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())))
            ->save();
    }
}
