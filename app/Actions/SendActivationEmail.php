<?php

namespace Statamic\Actions;

use Statamic\API;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;

class SendActivationEmail extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'users';
    }

    public function authorize($user)
    {
        return user()->can('sendActivationEmail', $user);
    }

    public function run($users)
    {
        $users->each->generateTokenAndSendPasswordResetNotification();
    }
}
