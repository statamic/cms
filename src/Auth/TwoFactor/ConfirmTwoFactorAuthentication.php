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
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            ! $this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        $user->set('two_factor_confirmed_at', now()->timestamp);
        $user->save();
    }
}
