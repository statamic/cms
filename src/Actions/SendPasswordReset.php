<?php

namespace Statamic\Actions;

use Statamic\Facades\User;

class SendPasswordReset extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'users';
    }

    public function authorize($user)
    {
        return User::current()->can('sendPasswordReset', $user);
    }

    public function run($users)
    {
        $users->each->generateTokenAndSendPasswordResetNotification();
    }
}
