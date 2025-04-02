<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\User;

/**
 * @method static ?string getLastChallenged(?\Statamic\Contracts\Auth\User $user = null)
 * @method static User get()
 * @method static static setLastChallenged(?User $user = null)
 * @method static static clearLastChallenged(?User $user = null)
 * @method static bool isTwoFactorEnforceable(?User $user = null)
 *
 * @see \Statamic\Auth\TwoFactor\StatamicTwoFactorUser
 */
class TwoFactorUser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'statamicTwoFactorUser';
    }
}
