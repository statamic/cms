<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class FieldsetPolicy
{
    public function before($user, $ability, $fieldset)
    {
        $user = User::fromUser($user);

        if ($user->isSuper() || $user->hasPermission('configure fields')) {
            return true;
        }
    }

    public function edit($user, $fieldset)
    {
        // handled by before()
    }

    public function delete($user, $fieldset)
    {
        // handled by before()
    }
}
