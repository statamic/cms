<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DisableTwoFactorAuthenticationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DisableTwoFactorAuthentication::class);
    }

    #[Test]
    public function it_correctly_removes_two_factor_authentication_from_the_user()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_completed);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNotNull($user->two_factor_secret);

        $this->action->__invoke($user);

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_completed);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
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
}
