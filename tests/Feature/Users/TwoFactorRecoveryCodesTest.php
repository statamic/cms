<?php

namespace Feature\Users;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
#[Group('2fa')]
class TwoFactorRecoveryCodesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_recovery_codes()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->get(cp_route('users.two-factor.recovery-codes.show'))
            ->assertOk()
            ->assertJson([
                'recovery_codes' => $user->twoFactorRecoveryCodes(),
            ]);
    }

    #[Test]
    public function it_does_not_return_recovery_codes_without_elevated_session()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->get(cp_route('users.two-factor.recovery-codes.show'))
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function it_generates_recovery_codes()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->post(cp_route('users.two-factor.recovery-codes.generate'))
            ->assertOk()
            ->assertJsonStructure(['recovery_codes']);
    }

    #[Test]
    public function it_cannot_generate_recovery_codes_without_elevated_session()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->post(cp_route('users.two-factor.recovery-codes.generate'))
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function it_can_download_recovery_codes()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->get(cp_route('users.two-factor.recovery-codes.download'))
            ->assertOk()
            ->assertSeeInOrder($user->twoFactorRecoveryCodes());
    }

    #[Test]
    public function it_cannot_download_recovery_codes_without_elevated_session()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->get(cp_route('users.two-factor.recovery-codes.download'))
            ->assertRedirect('/cp/auth/confirm-password');
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_confirmed_at' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }

    private function withActiveElevatedSession()
    {
        return $this->session(['statamic_elevated_session' => now()->timestamp]);
    }
}
