<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Validation\ValidationException;
use Statamic\Auth\User;

class ConfirmTwoFactorAuthentication
{
    public function __construct(private TwoFactorAuthenticationProvider $provider)
    {
    }

    public function __invoke(User $user, ?string $code)
    {
        if (
            empty($user->two_factor_secret)
            || empty($code)
            || ! $this->provider->verify($user->twoFactorSecretKey(), $code)
        ) {
            throw ValidationException::withMessages([
                'code' => [__('statamic::validation.invalid_two_factor_code')],
            ]);
        }

        $user->set('two_factor_confirmed_at', now()->timestamp)->save();
    }
}
