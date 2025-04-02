<?php

namespace Statamic\Auth\TwoFactor;

use Statamic\Auth\User;

class UnlockUser
{
    public function __invoke(User $user)
    {
        // update the user
        $user->set('two_factor_locked', false);
        $user->save();
    }
}
