<?php

namespace Actions;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\DisableTwoFactorAuthentication as Action;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DisableTwoFactorTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_disables_two_factor()
    {
        // Since we don't want this action to be usable on bulk actions, we'll pass
        // two users into run, but make sure it only works for the first one.

        $user = User::make();
        $user2 = User::make();

        $mock = Mockery::mock(DisableTwoFactorAuthentication::class)
            ->shouldReceive('__invoke')
            ->with($user)
            ->once()
            ->getMock();

        $this->app->instance(DisableTwoFactorAuthentication::class, $mock);

        (new Action)->run(collect([$user, $user2]), []);
    }

    #[Test]
    public function its_only_visible_for_users_with_two_factor_enabled()
    {
        $userWithout2fa = User::make();
        $userWith2fa = User::make()
            ->set('two_factor_secret', 'secret')
            ->set('two_factor_confirmed_at', now());

        $this->assertFalse((new Action)->visibleTo($userWithout2fa));
        $this->assertTrue((new Action)->visibleTo($userWith2fa));
    }

    #[Test]
    public function it_does_not_disable_two_factor_if_current_user_doesnt_have_permission()
    {
        $this->setTestRoles([
            'access' => ['change passwords'],
            'noaccess' => [],
        ]);

        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();

        $items = collect([User::make(), User::make()]);

        $this->assertTrue((new Action)->authorize($userWithPermission, $items->first()));
        $this->assertFalse((new Action)->authorize($userWithoutPermission, $items->first()));

        // Not allowed for bulk at all.
        $this->assertFalse((new Action)->authorizeBulk($userWithPermission, $items));
        $this->assertFalse((new Action)->authorizeBulk($userWithoutPermission, $items));
    }
}
