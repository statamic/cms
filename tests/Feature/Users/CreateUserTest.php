<?php

namespace Feature\Users;

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
}
