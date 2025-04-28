<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Exceptions\ElevatedSessionAuthorizationException;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRecoveryCodesControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_recovery_codes()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->get(cp_route('users.two-factor.recovery-codes.show', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson([
                'recovery_codes' => $user->recoveryCodes(),
            ]);
    }

    #[Test]
    public function it_does_not_return_recovery_codes_without_elevated_session()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->get(cp_route('users.two-factor.recovery-codes.show', [
                'user' => $user->id,
            ]))
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function it_does_not_return_recovery_codes_for_another_user()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->get(cp_route('users.two-factor.recovery-codes.show', [
                'user' => $this->userWithTwoFactorEnabled()->id,
            ]))
            ->assertForbidden();
    }

    #[Test]
    public function it_generates_recovery_codes()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->post(cp_route('users.two-factor.recovery-codes.generate', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJsonStructure(['recovery_codes']);
    }

    #[Test]
    public function it_cannot_generate_recovery_codes_without_elevated_session()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->post(cp_route('users.two-factor.recovery-codes.generate', [
                'user' => $user->id,
            ]))
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function it_cannot_generate_recovery_codes_for_another_user()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->post(cp_route('users.two-factor.recovery-codes.generate', [
                'user' => $this->userWithTwoFactorEnabled()->id,
            ]))
            ->assertForbidden();
    }

    #[Test]
    public function it_can_download_recovery_codes()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->get(cp_route('users.two-factor.recovery-codes.download', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertSeeInOrder($user->recoveryCodes());
    }

    #[Test]
    public function it_cannot_download_recovery_codes_without_elevated_session()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->get(cp_route('users.two-factor.recovery-codes.download', [
                'user' => $user->id,
            ]))
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function it_cannot_download_recovery_codes_for_another_user()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->get(cp_route('users.two-factor.recovery-codes.download', [
                'user' => $this->userWithTwoFactorEnabled()->id,
            ]))
            ->assertForbidden();
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
