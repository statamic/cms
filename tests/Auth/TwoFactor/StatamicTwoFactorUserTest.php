<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\Role;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StatamicTwoFactorUserTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_the_current_user()
    {
        $this->assertNull(TwoFactorUser::get());

        $this->actingAs($user = $this->user());

        $this->assertInstanceOf(\Statamic\Auth\User::class, $user);
        $this->assertEquals($user->id(), TwoFactorUser::get()->id());
    }

    #[Test]
    public function it_sets_gets_and_clears_the_last_challenged_for_the_user()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());
        $otherUser = $this->userWithTwoFactorEnabled();

        $this->assertNull(TwoFactorUser::getLastChallenged());
        $this->assertNull(TwoFactorUser::getLastChallenged($otherUser));

        TwoFactorUser::setLastChallenged();

        $this->assertNotNull(TwoFactorUser::getLastChallenged());
        $this->assertNull(TwoFactorUser::getLastChallenged($otherUser));

        TwoFactorUser::clearLastChallenged();

        $this->assertNull(TwoFactorUser::getLastChallenged());
        $this->assertNull(TwoFactorUser::getLastChallenged($otherUser));

        // Works with specific users...
        $this->assertNull(TwoFactorUser::getLastChallenged($user));
        $this->assertNull(TwoFactorUser::getLastChallenged($otherUser));

        TwoFactorUser::setLastChallenged($otherUser);

        $this->assertNull(TwoFactorUser::getLastChallenged($user));
        $this->assertNotNull(TwoFactorUser::getLastChallenged($otherUser));

        TwoFactorUser::clearLastChallenged($otherUser);

        $this->assertNull(TwoFactorUser::getLastChallenged($user));
        $this->assertNull(TwoFactorUser::getLastChallenged($otherUser));
    }

    #[Test]
    public function it_determines_if_two_factor_is_required_for_a_non_super_user_with_roles()
    {
        $role = Role::make('enforceable_role')->save();

        $user = $this->user();
        $user->assignRole($role)->save();

        config()->set('statamic.users.two_factor.enforced_roles', []);
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable($user));

        config()->set('statamic.users.two_factor.enforced_roles', ['enforceable_role']);
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable($user));

        config()->set('statamic.users.two_factor.enforced_roles', ['*']);
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable($user));
    }

    #[Test]
    public function it_determines_if_two_factor_is_required_for_a_non_super_user_without_roles()
    {
        $user = $this->user();

        config()->set('statamic.users.two_factor.enforced_roles', []);
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable($user));

        config()->set('statamic.users.two_factor.enforced_roles', ['enforceable_role']);
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable($user));

        config()->set('statamic.users.two_factor.enforced_roles', ['*']);
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable($user));
    }

    #[Test]
    public function it_determines_if_two_factor_is_required_for_a_super_user()
    {
        $user = $this->user();
        $user->makeSuper()->save();

        config()->set('statamic.users.two_factor.enforced_roles', []);
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable($user));

        config()->set('statamic.users.two_factor.enforced_roles', ['super_users']);
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable($user));

        config()->set('statamic.users.two_factor.enforced_roles', ['*']);
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable($user));
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
