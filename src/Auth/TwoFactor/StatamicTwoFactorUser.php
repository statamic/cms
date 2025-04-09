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
        $user = $user ?: $this->get();

        if (! $user) {
            return false;
        }

        $enforcedRoles = config('statamic.users.two_factor.enforced_roles', []);

        if (in_array('*', $enforcedRoles)) {
            return true;
        }

        return $user->roles()
            ->map->handle()
            ->when($user->isSuper(), fn ($roles) => $roles->push('super_users'))
            ->intersect($enforcedRoles)
            ->isNotEmpty();
    }
}
