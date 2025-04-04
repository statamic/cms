<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\ChallengeTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Exceptions\InvalidChallengeModeException;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Notifications\RecoveryCodeUsed;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConfirmTwoFactorAuthenticationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $user;
    private $action;
    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = app(Google2FA::class);

        $this->user = User::make()->makeSuper()->data([
            'two_factor_confirmed_at' => null,
            'two_factor_completed' => null,
            'two_factor_locked' => false,
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $this->user->save();

        $this->actingAs($this->user);

        $this->action = app(ConfirmTwoFactorAuthentication::class);
    }

    #[Test]
    public function throws_an_exception_when_no_one_time_code_is_present()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two factor authentication code was invalid.');

        $this->action->__invoke($this->user, null);
    }

    #[Test]
    public function throws_a_validation_exception_when_an_invalid_one_time_code_is_present()
    {
        // Get a one-time code (so we can make sure we have a wrong one in the test)
        $code = $this->getOneTimeCode();

        // If our actual code is 111111, then output 222222
        if ($code === '111111') {
            $code = '222222';
        } else {
            $code = '111111';
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two factor authentication code was invalid.');

        // Attempt challenge without passing a one-time code.
        $this->action->__invoke($this->user, $code);
    }

    #[Test]
    public function correctly_confirms_two_factor_authentication()
    {
        $this->freezeTime();

        $code = $this->getOneTimeCode();

        $this->assertNull(session()->get('statamic_two_factor'));
        $this->assertNull($this->user->two_factor_confirmed_at);

        $this->action->__invoke($this->user, $code);

        $this->assertNotNull($this->user->two_factor_confirmed_at);
        $this->assertEquals(now(), $this->user->two_factor_confirmed_at);

        $this->assertEquals(now(), TwoFactorUser::getLastChallenged($this->user));
    }

    private function getOneTimeCode()
    {
        $provider = app(Google2FA::class);

        // get a one-time code (so we can make sure we have a wrong one in the test)
        $internalProvider = app(\PragmaRX\Google2FA\Google2FA::class);

        return $internalProvider->getCurrentOtp($provider->getSecretKey());
    }
}
