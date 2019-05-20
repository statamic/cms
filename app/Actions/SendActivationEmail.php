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

    public function run($users)
    {
        $users->each->generateTokenAndSendPasswordResetNotification();
    }
}
