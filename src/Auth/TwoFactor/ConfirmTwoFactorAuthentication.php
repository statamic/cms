<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Validation\ValidationException;
use Statamic\Auth\User;

class ConfirmTwoFactorAuthentication
{
    public function __construct(private Google2FA $provider)
    {
    }

    public function __invoke(User $user, ?string $code)
    {
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            ! $this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'code' => [__('statamic::validation.two_factor_code_challenge_failed')],
            ]);
        }

        // update the user
        $user->set('two_factor_confirmed_at', now());
        $user->save();

        // update (prevents going to the challenge screen after setup)
        $user->setLastTwoFactorChallenged();
    }
}
