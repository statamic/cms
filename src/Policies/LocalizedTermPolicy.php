<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class LocalizedTermPolicy extends TermPolicy
{
    public function edit($user, $term)
    {
        $user = User::fromUser($user);

        if (! $this->userCanAccessSite($user, $term->site())) {
            return false;
        }

        return $user->hasPermission("edit {$term->taxonomyHandle()} terms");
    }
}
