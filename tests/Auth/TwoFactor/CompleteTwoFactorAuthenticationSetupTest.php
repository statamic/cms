<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\ChallengeTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\CompleteTwoFactorAuthenticationSetup;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Exceptions\InvalidChallengeModeException;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Notifications\RecoveryCodeUsed;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CompleteTwoFactorAuthenticationSetupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CompleteTwoFactorAuthenticationSetup::class);
    }

    #[Test]
    public function it_correctly_updates_the_user_as_having_two_factor_enabled()
    {
        $this->freezeTime();

        $user = tap(User::make()->makeSuper())->save();

        $this->assertNull($user->two_factor_completed);

        $this->action->__invoke($user);

        $this->assertNotNull($user->two_factor_completed);
        $this->assertEquals(now(), $user->two_factor_completed);
    }
}
