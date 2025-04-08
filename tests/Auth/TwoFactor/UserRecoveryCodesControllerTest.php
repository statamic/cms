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

class UserRecoveryCodesControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnforceTwoFactor::class);
    }

    #[Test]
    public function it_returns_a_403_when_trying_to_see_codes_for_another_user()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('users.two-factor.recovery-codes.show', [
                'user' => $this->userWithTwoFactorEnabled()->id,
            ]))
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_the_recovery_codes_for_the_logged_in_user()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->get(cp_route('users.two-factor.recovery-codes.show', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson([
                'recovery_codes' => json_decode(decrypt($user->two_factor_recovery_codes), true),
            ]);
    }

    #[Test]
    public function it_returns_a_403_when_trying_to_generate_codes_for_another_user()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->post(cp_route('users.two-factor.recovery-codes.generate', [
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
            ->post(cp_route('users.two-factor.recovery-codes.generate', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJsonStructure(['recovery_codes']);
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
