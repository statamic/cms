<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\Role;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\EnforceTwoFactor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserResetControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnforceTwoFactor::class);
    }

    #[Test]
    public function it_uses_the_disable_two_factor_authentication_action()
    {
        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.reset', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);
    }

    #[Test]
    public function it_provides_the_logout_route_as_a_redirect_if_reset_is_self_and_enforceable()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable($user));

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.reset', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => cp_route('logout')]);
    }

    #[Test]
    public function it_does_not_provide_the_logout_route_as_a_redirect_if_reset_is_self_and_not_enforceable()
    {
        // No roles enforced
        config()->set('statamic.users.two_factor.enforced_roles', []);

        // Create special user
        $user = $this->userWithTwoFactorEnabled();

        $role = Role::make('enforceable_role')
            ->addPermission('access cp')
            ->addPermission('view users')
            ->addPermission('edit users')
            ->save();

        $user->set('super', false)->assignRole($role)->save();

        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable($user));

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.reset', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);
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
