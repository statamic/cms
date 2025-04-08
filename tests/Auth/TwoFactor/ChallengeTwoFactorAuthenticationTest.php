<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\ChallengeTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Exceptions\InvalidChallengeModeException;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Notifications\RecoveryCodeUsed;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ChallengeTwoFactorAuthenticationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ChallengeTwoFactorAuthentication::class);
    }

    #[Test]
    public function it_throws_an_exception_when_two_factor_is_not_enabled()
    {
        $this->expectException(ValidationException::class);

        $user = $this->user();

        $this->actingAs($user);

        $this->action->__invoke($user, '', '');
    }

    #[Test]
    public function it_throws_an_invalid_challenge_mode_exception_when_an_invalid_mode_is_presented()
    {
        $this->expectException(InvalidChallengeModeException::class);

        $this->action->__invoke($this->userWithTwoFactorEnabled(), '', '');
    }

    #[Test]
    public function it_throws_a_validation_exception_when_a_no_one_time_code_is_present()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two factor authentication code was invalid.');

        $this->action->__invoke($this->userWithTwoFactorEnabled(), 'code', null);
    }

    #[Test]
    public function it_throws_a_validation_exception_when_an_invalid_one_time_code_is_present()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

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
        $this->action->__invoke($user, 'code', $code);
    }

    #[Test]
    public function it_correctly_accepts_a_one_time_code_challenge()
    {
        $this->freezeTime();
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $code = $this->getOneTimeCode();

        $this->assertNull(TwoFactorUser::getLastChallenged($user));

        $this->action->__invoke($user, 'code', $code);

        $this->assertEquals(now(), TwoFactorUser::getLastChallenged($user));
    }

    #[Test]
    public function it_throws_a_validation_exception_when_no_recovery_code_is_presented()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two factor authentication code was invalid.');

        $this->action->__invoke($this->userWithTwoFactorEnabled(), 'recovery_code', null);
    }

    #[Test]
    public function it_throws_a_validation_exception_when_an_invalid_recovery_code_is_presented()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two factor authentication code was invalid.');

        $this->action->__invoke($this->userWithTwoFactorEnabled(), 'recovery_code', 'invalid-code');
    }

    #[Test]
    public function it_correctly_accepts_a_recovery_code_challenge()
    {
        $this->freezeTime();
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $this->assertNull(TwoFactorUser::getLastChallenged($user));

        $userRecoveryCode = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))->first();

        $this->action->__invoke($user, 'recovery_code', $userRecoveryCode);

        $this->assertEquals(now(), TwoFactorUser::getLastChallenged($user));
    }

    #[Test]
    public function it_removes_and_replaces_the_used_recovery_code_on_a_successful_usage()
    {
        $user = $this->userWithTwoFactorEnabled();

        $recoveryCodes = $originalRecoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        $userRecoveryCode = array_slice($recoveryCodes, 0, 3)[0]; // Get a code in the middle of the pack

        $this->action->__invoke($user, 'recovery_code', $userRecoveryCode);

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        // We should still have 8 codes, but the one we used should be removed.
        $this->assertCount(8, $recoveryCodes);
        $this->assertNotContains($userRecoveryCode, $recoveryCodes);
        $this->assertNotSame($originalRecoveryCodes, $recoveryCodes);

        collect($recoveryCodes)
            ->filter(fn ($recoveryCode) => $recoveryCode != $userRecoveryCode)
            ->each(fn ($code) => $this->assertContains($code, $recoveryCodes));
    }

    #[Test]
    public function it_sends_the_recovery_code_used_notification_when_a_recovery_code_is_successfully_used()
    {
        Notification::fake();

        $user = $this->userWithTwoFactorEnabled();

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        $userRecoveryCode = array_slice($recoveryCodes, 0, 3)[0]; // Get a code in the middle of the pack

        $this->action->__invoke($user, 'recovery_code', $userRecoveryCode);

        Notification::assertSentTo($user, RecoveryCodeUsed::class);
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_locked' => false,
            'two_factor_confirmed_at' => now(),
            'two_factor_completed' => now(),
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }

    private function getOneTimeCode()
    {
        $provider = app(Google2FA::class);

        // get a one-time code (so we can make sure we have a wrong one in the test)
        $internalProvider = app(\PragmaRX\Google2FA\Google2FA::class);

        return $internalProvider->getCurrentOtp($provider->getSecretKey());
    }
}
