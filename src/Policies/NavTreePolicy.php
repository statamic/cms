<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class NavTreePolicy extends NavPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user)
    {
        if (User::fromUser($user)->isSuper()) {
            return true;
        }
    }

    public function view($user, $nav)
    {
        $user = User::fromUser($user);

        if (! $this->userCanAccessSite($user, $nav->site())) {
            return false;
        }

        return $user->hasPermission("view {$nav->handle()} nav");
    }

    public function edit($user, $nav)
    {
        $user = User::fromUser($user);

        if (! $this->userCanAccessSite($user, $nav->site())) {
            return false;
        }

        return $user->hasPermission("edit {$nav->handle()} nav");
    }
}
