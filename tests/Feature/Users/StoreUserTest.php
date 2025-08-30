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
class StoreUserTest extends TestCase
{
    use ElevatesSessions;
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function store($data = [])
    {
        return $this->postJson(route('statamic.cp.users.store'), array_merge([
            'email' => 'test@domain.com',
            'invitation' => ['send' => false],
        ], $data));
    }

    #[Test]
    public function it_creates_a_user()
    {
        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAsWithElevatedSession($me)
            ->store()
            ->assertOk();
    }

    #[Test]
    public function it_requires_an_elevated_session()
    {
        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->store()
            ->assertElevatedSessionRequiredJsonResponse();
    }
}
