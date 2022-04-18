<?php

namespace Statamic\Actions;

use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\User as UserContract;

class CopyPasswordResetLink extends Action
{
    public static function title()
    {
        return __('Copy Password Reset Link');
    }

    public function visibleTo($item)
    {
        return $item instanceof UserContract;
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($authed, $user)
    {
        return $authed->can('sendPasswordReset', $user);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Copy password reset email for this user?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Copy';
    }

    public function alert($items, $values)
    {
        $user = $items->first();

        $passwordResetLink = $user->password()
            ? PasswordReset::url($user->generatePasswordResetToken(), PasswordReset::BROKER_RESETS)
            : PasswordReset::url($user->generatePasswordResetToken(), PasswordReset::BROKER_ACTIVATIONS);

        return "Password Reset Link: {$passwordResetLink}";
    }
}
