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
    public function gets_the_current_user()
    {
        $this->assertNull(TwoFactorUser::get());

        $this->actingAs($user = $this->user());

        $this->assertInstanceOf(\Statamic\Auth\User::class, $user);
        $this->assertEquals($user->id(), TwoFactorUser::get()->id());
    }

    #[Test]
    public function sets_gets_and_clears_the_last_challenged_for_the_user()
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
    public function correctly_determines_if_two_factor_is_enforcable()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $enforceableRole = Role::make('enforceable_role')->save();
        $standardRole = Role::make('standard_role')->save();

        // Enforced for all users, never mind their roles.
        config()->set('statamic.users.two_factor.enforced_roles', null);

        $user->makeSuper()->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable());
        $user->set('super', false)->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable());

        $user->assignRole($enforceableRole)->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable());

        $user->removeRole($enforceableRole)->assignRole($standardRole)->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable());

        // Enforced for no one.
        config()->set('statamic.users.two_factor.enforced_roles', []);

        $user->makeSuper()->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable()); // Super users are always enforced.
        $user->set('super', false)->save();
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable());

        $user->assignRole($enforceableRole)->save();
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable());

        $user->removeRole($enforceableRole)->assignRole($standardRole)->save();
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable());

        // Enforced for specific roles.
        config()->set('statamic.users.two_factor.enforced_roles', [
            'enforceable_role',
        ]);

        $user->makeSuper()->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable()); // Super users are always enforced.
        $user->set('super', false)->save();
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable());

        $user->assignRole($enforceableRole)->save();
        $this->assertTrue(TwoFactorUser::isTwoFactorEnforceable());

        $user->removeRole($enforceableRole)->assignRole($standardRole)->save();
        $this->assertFalse(TwoFactorUser::isTwoFactorEnforceable());
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
