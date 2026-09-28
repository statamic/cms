<?php

namespace Tests\Actions;

use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\Impersonate as Action;
use Statamic\Facades\User;
use Statamic\Policies\UserPolicy;
use Tests\ElevatesSessions;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class ImpersonateTest extends TestCase
{
    use ElevatesSessions;
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function impersonate($user)
    {
        return $this->post(cp_route('users.actions.run'), [
            'action' => 'impersonate',
            'context' => [],
            'selections' => [$user->id()],
            'values' => [],
        ]);
    }

    #[Test]
    public function it_authenticates_as_another_user_and_clears_elevated_session()
    {
        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper()->password('secret1'))->save();
        $impersonated = tap(User::make()->email('user@example.com')->password('secret2'))->save();

        $this->actingAs($impersonator)->withElevatedSession();

        $this->assertEquals($impersonator->id(), auth()->id());
        $this->assertTrue(request()->hasElevatedSession());

        $this->impersonate($impersonated);

        $this->assertEquals($impersonated->id(), auth()->id());
        $this->assertFalse(request()->hasElevatedSession());
    }

    #[Test]
    public function it_is_visible_to_a_valid_target_user()
    {
        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper())->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        $this->actingAs($impersonator);

        $this->assertTrue((new Action)->visibleTo($impersonated));
    }

    #[Test]
    public function it_is_not_visible_when_policy_denies_impersonation()
    {
        $this->setTestRoles(['impersonator' => ['impersonate users']]);

        $impersonator = tap(User::make()->email('admin@example.com')->assignRole('impersonator'))->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        Gate::policy(get_class($impersonated), DenyImpersonationPolicy::class);

        $this->actingAs($impersonator);

        $this->assertFalse((new Action)->visibleTo($impersonated));
    }

    #[Test]
    public function it_is_authorized_with_the_default_policy()
    {
        $this->setTestRoles(['impersonator' => ['impersonate users']]);

        $impersonator = tap(User::make()->email('admin@example.com')->assignRole('impersonator'))->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        $this->assertTrue((new Action)->authorize($impersonator, $impersonated));
    }

    #[Test]
    public function it_is_not_authorized_when_policy_denies_impersonation()
    {
        $this->setTestRoles(['impersonator' => ['impersonate users']]);

        $impersonator = tap(User::make()->email('admin@example.com')->assignRole('impersonator'))->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        Gate::policy(get_class($impersonated), DenyImpersonationPolicy::class);

        $this->assertFalse((new Action)->authorize($impersonator, $impersonated));
    }

    #[Test]
    public function it_is_not_authorized_without_permission()
    {
        $this->setTestRoles(['editor' => ['edit users']]);

        $impersonator = tap(User::make()->email('admin@example.com')->assignRole('editor'))->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        $this->assertFalse((new Action)->authorize($impersonator, $impersonated));
    }

    #[Test]
    public function super_users_bypass_the_policy_check()
    {
        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper())->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        Gate::policy(get_class($impersonated), DenyImpersonationPolicy::class);

        $this->actingAs($impersonator);

        $this->assertTrue((new Action)->visibleTo($impersonated));
        $this->assertTrue((new Action)->authorize($impersonator, $impersonated));
    }
}

class DenyImpersonationPolicy extends UserPolicy
{
    public function impersonate($authed, $user)
    {
        return false;
    }
}
