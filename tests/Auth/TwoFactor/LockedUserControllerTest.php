<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LockedUserControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_redirects_to_the_dashboard_if_the_user_is_not_locked()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->assertFalse($user->two_factor_locked);

        $this
            ->actingAs($user)
            ->get(cp_route('two-factor.locked'))
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function it_logs_the_user_out_and_returns_the_locked_view_when_the_user_is_locked_out()
    {
        $user = $this->userWithTwoFactorEnabled();

        $user->set('two_factor_locked', true)->save();

        $this->assertTrue($user->two_factor_locked);

        $this
            ->actingAs($user)
            ->get(cp_route('two-factor.locked'))
            ->assertViewIs('statamic::auth.two-factor.locked');

        // Ensure the user is now logged out
        $this->assertNull(User::current());
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
