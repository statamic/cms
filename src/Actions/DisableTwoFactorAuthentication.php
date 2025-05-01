<?php

namespace Statamic\Actions;

class DisableTwoFactorAuthentication extends Action
{
    protected $dangerous = true;

    public function confirmationText()
    {
        return $this->items->first()->isTwoFactorAuthenticationRequired()
            ? __('statamic::messages.disable_two_factor_authentication_other_user_enforced')
            : __('statamic::messages.disable_two_factor_authentication_other_user_optional');
    }

    public function buttonText()
    {
        return __('Confirm');
    }

    public function visibleTo($item)
    {
        return $item->hasEnabledTwoFactorAuthentication();
    }

    public function authorize($user, $item)
    {
        return $user->can('change passwords', $item);
    }

    public function authorizeBulk($user, $items)
    {
        return false;
    }

    public function run($items, $values)
    {
        app(\Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication::class)($items->first());
    }
}
