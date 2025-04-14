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

class DisableTwoFactorControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnforceTwoFactor::class);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_the_current_user()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_completed);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_the_current_user_when_two_factor_is_enforced()
    {
        // Enforced for everyone
        config()->set('statamic.users.two_factor.enforced_roles', ['*']);

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => cp_route('logout')]);

        $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_completed);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_another_user()
    {
        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $otherUser->fresh();

        $this->assertNull($otherUser->two_factor_confirmed_at);
        $this->assertNull($otherUser->two_factor_completed);
        $this->assertNull($otherUser->two_factor_recovery_codes);
        $this->assertNull($otherUser->two_factor_secret);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_another_user_when_two_factor_is_enforced()
    {
        // Enforced for everyone
        config()->set('statamic.users.two_factor.enforced_roles', ['*']);

        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $otherUser->fresh();

        $this->assertNull($otherUser->two_factor_confirmed_at);
        $this->assertNull($otherUser->two_factor_completed);
        $this->assertNull($otherUser->two_factor_recovery_codes);
        $this->assertNull($otherUser->two_factor_secret);
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
