<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Support\Facades\Config;
use Statamic\Facades\User;

class StatamicTwoFactorUser
{
    public function getLastChallenged(?\Statamic\Contracts\Auth\User $user = null): ?string
    {
        // get the user
        if (! $user) {
            $user = $this->get();
        }

        $lastChallenged = null;

        // no user, return
        if (! $user) {
            return $lastChallenged;
        }

        // are we using eloquent or flat file
        if (Config::get('statamic.users.repository') === 'eloquent') {
            $lastChallenged = $user->get('two_factor_last_challenged', null);
        } else {
            $lastChallenged = $user->getMeta('statamic_two_factor', null);
        }

        // if we have a challenge, decrypt it
        if ($lastChallenged) {
            $lastChallenged = decrypt($lastChallenged);
        }

        return $lastChallenged;
    }

    public function get(): ?\Statamic\Contracts\Auth\User
    {
        return User::current();
    }

    public function setLastChallenged(?\Statamic\Contracts\Auth\User $user = null): static
    {
        // get the user
        if (! $user) {
            $user = $this->get();
        }

        if (! $user) {
            return $this;
        }

        // are we using eloquent or flat file
        if (Config::get('statamic.users.repository') === 'eloquent') {
            $user->set('two_factor_last_challenged', encrypt(now()));
            $user->save();
        } else {
            $user->setMeta('statamic_two_factor', encrypt(now()));
        }

        return $this;
    }

    public function clearLastChallenged(?\Statamic\Contracts\Auth\User $user = null): static
    {
        // get the user
        if (! $user) {
            $user = $this->get();
        }

        if (! $user) {
            return $this;
        }

        // are we using eloquent or flat file
        if (Config::get('statamic.users.repository') === 'eloquent') {
            $user->set('two_factor_last_challenged', null);
            $user->save();
        } else {
            $user->setMeta('statamic_two_factor', null);
        }

        return $this;
    }

    public function isTwoFactorEnforceable(?\Statamic\Contracts\Auth\User $user = null): bool
    {
        if (! $user) {
            $user = $this->get();
        }

        // no user - so not enforceable
        if (! $user) {
            return false;
        }

        // super admin are always enforced

        if ($user->isSuper()) {
            return true;
        }

        // get configured enforced roles
        $enforcedRoles = config('statamic.users.two_factor.enforced_roles', null);

        // null means all roles are enforced
        if ($enforcedRoles === null) {
            return true;
        }

        // if an array of roles check if the user contains ANY of them
        if (is_array($enforcedRoles)) {
            foreach ($enforcedRoles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false; // this far, not enforced
    }
}
