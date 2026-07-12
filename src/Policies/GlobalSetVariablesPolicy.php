<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class GlobalSetVariablesPolicy extends GlobalSetPolicy
{
    use Concerns\HasMultisitePolicy;

    public function view($user, $variables)
    {
        $user = User::fromUser($user);

        if (! $this->userCanAccessSite($user, $variables->site())) {
            return false;
        }

        return $user->hasPermission("edit {$variables->handle()} globals");
    }

    public function edit($user, $variables)
    {
        return $this->view($user, $variables);
    }
}
