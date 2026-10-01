<?php

namespace Tests\Actions;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\Impersonate as Action;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ImpersonateTest extends TestCase
{
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
    public function it_authenticates_as_another_user()
    {
        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper()->password('secret1'))->save();
        $impersonated = tap(User::make()->email('user@example.com')->password('secret2'))->save();

        $this->actingAs($impersonator);

        $this->impersonate($impersonated);

        $this->assertEquals($impersonated->id(), auth()->id());
    }

    #[Test]
    public function it_cannot_be_run_when_impersonation_is_disabled()
    {
        config(['statamic.users.impersonate.enabled' => false]);

        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper()->password('secret1'))->save();
        $impersonated = tap(User::make()->email('user@example.com')->password('secret2'))->save();

        $this->actingAs($impersonator);

        $this->impersonate($impersonated)->assertForbidden();

        $this->assertEquals($impersonator->id(), auth()->id());
    }

    #[Test]
    public function it_cannot_be_run_while_already_impersonating()
    {
        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper()->password('secret1'))->save();
        $impersonated = tap(User::make()->email('user@example.com')->makeSuper()->password('secret2'))->save();
        $target = tap(User::make()->email('target@example.com')->password('secret3'))->save();

        $this->actingAs($impersonated)
            ->withSession(['statamic_impersonated_by' => $impersonator->id()]);

        $this->impersonate($target)->assertForbidden();

        $this->assertEquals($impersonated->id(), auth()->id());
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
    public function it_is_not_visible_when_impersonation_is_disabled()
    {
        config(['statamic.users.impersonate.enabled' => false]);

        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper())->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        $this->actingAs($impersonator);

        $this->assertFalse((new Action)->visibleTo($impersonated));
    }

    #[Test]
    public function it_is_not_visible_for_the_current_user()
    {
        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper())->save();

        $this->actingAs($impersonator);

        $this->assertFalse((new Action)->visibleTo($impersonator));
    }

    #[Test]
    public function it_is_authorized_with_permission()
    {
        $this->setTestRoles(['impersonator' => ['impersonate users']]);

        $impersonator = tap(User::make()->email('admin@example.com')->assignRole('impersonator'))->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        $this->assertTrue((new Action)->authorize($impersonator, $impersonated));
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
    public function it_is_not_authorized_when_impersonation_is_disabled()
    {
        config(['statamic.users.impersonate.enabled' => false]);

        $impersonator = tap(User::make()->email('admin@example.com')->makeSuper())->save();
        $impersonated = tap(User::make()->email('user@example.com'))->save();

        $this->assertFalse((new Action)->authorize($impersonator, $impersonated));
    }
}
