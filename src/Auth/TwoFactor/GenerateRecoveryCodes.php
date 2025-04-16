<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Statamic\Auth\User;

class GenerateRecoveryCodes
{
    public function __invoke(User $user)
    {
        $recoveryCodes = Collection::times(8, function () {
            return RecoveryCode::generate();
        })->all();

        $user->set('two_factor_recovery_codes', encrypt(json_encode($recoveryCodes)))->save();
    }
}
