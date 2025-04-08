<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EnableTwoFactorAuthenticationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(EnableTwoFactorAuthentication::class);
    }

    #[Test]
    public function it_correctly_updates_the_user_as_partially_setup()
    {
        $this->freezeTime();
        $user = tap(User::make()->makeSuper())->save();

        $this->assertNull($user->two_factor_completed);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);

        $this->action->__invoke($user, true);

        $this->assertNull($user->two_factor_completed);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNotNull($user->two_factor_secret);

        // Store existing codes and secret
        $recoveryCodes = $user->two_factor_recovery_codes;
        $secret = $user->two_factor_secret;

        // Re-action, WITHOUT resetting the secret
        $this->action->__invoke($user, false);

        $this->assertEquals($recoveryCodes, $user->two_factor_recovery_codes);
        $this->assertEquals($secret, $user->two_factor_secret);
    }
}
