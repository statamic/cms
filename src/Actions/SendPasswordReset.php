<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;

class SendPasswordReset extends Action
{
    public static function title()
    {
        return __('Send Password Reset');
    }

    public function visibleTo($item)
    {
        return $item instanceof UserContract;
    }

    public function authorize($authed, $user)
    {
        return $authed->can('sendPasswordReset', $user);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Send password reset email to this user?|Send password reset email to these :count users?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Send|Send to :count users';
    }

    public function run($users, $values)
    {
        $users->each(function ($user) {
            $user->password()
                ? $user->generateTokenAndSendPasswordResetNotification()
                : $user->generateTokenAndSendActivateAccountNotification();
        });
    }
}
