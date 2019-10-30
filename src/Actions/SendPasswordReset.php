<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

class SendPasswordReset extends Action
{
    public function filter($item)
    {
        return $item instanceof UserContract;
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
