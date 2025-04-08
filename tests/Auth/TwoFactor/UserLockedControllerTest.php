<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\EnforceTwoFactor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserLockedControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnforceTwoFactor::class);
    }

    #[Test]
    public function it_cannot_be_performed_on_yourself()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.unlock', $user->id))
            ->assertForbidden();

        // Try with another user
        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.unlock', $otherUser->id))
            ->assertOk();
    }

    #[Test]
    public function it_uses_the_unlock_user_action()
    {
        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.unlock', $otherUser->id))
            ->assertOk();
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
}
