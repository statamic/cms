<?php

namespace Tests\Actions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\ElevatesSessions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class ImpersonateTest extends TestCase
{
    use ElevatesSessions;
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
}
