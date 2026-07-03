<?php

namespace Tests\Feature\Users;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\ElevatesSessions;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class CreateUserTest extends TestCase
{
    use ElevatesSessions;
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAsWithElevatedSession($me)
            ->get(route('statamic.cp.users.create'))
            ->assertOk();
    }

    #[Test]
    public function it_requires_an_elevated_session()
    {
        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->get(route('statamic.cp.users.create'))
            ->assertRedirectToConfirmPasswordForElevatedSession();
    }

    #[Test]
    public function the_super_toggle_defaults_to_enabled()
    {
        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAsWithElevatedSession($me)
            ->get(route('statamic.cp.users.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/Create')
                ->where('defaultSuper', true));
    }

    #[Test]
    public function the_super_toggle_default_can_be_disabled_via_config()
    {
        config(['statamic.users.wizard_default_super' => false]);

        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAsWithElevatedSession($me)
            ->get(route('statamic.cp.users.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/Create')
                ->where('defaultSuper', false));
    }
}
