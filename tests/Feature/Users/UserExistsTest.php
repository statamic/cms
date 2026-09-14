<?php

namespace Tests\Feature\Users;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserExistsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_users_without_permission_to_view_users()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        tap(User::make()->email('existing@domain.com'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->postJson(cp_route('user.exists'), ['email' => 'existing@domain.com'])
            ->assertForbidden();
    }

    #[Test]
    public function it_reports_an_existing_user_for_authorized_users()
    {
        $this->setTestRoles(['test' => ['access cp', 'view users']]);
        tap(User::make()->email('existing@domain.com'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->postJson(cp_route('user.exists'), ['email' => 'existing@domain.com'])
            ->assertOk()
            ->assertExactJson(['exists' => true]);
    }

    #[Test]
    public function it_reports_a_missing_user_for_authorized_users()
    {
        $this->setTestRoles(['test' => ['access cp', 'view users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->postJson(cp_route('user.exists'), ['email' => 'unknown@domain.com'])
            ->assertOk()
            ->assertExactJson(['exists' => false]);
    }

    #[Test]
    public function it_allows_super_users()
    {
        tap(User::make()->email('existing@domain.com'))->save();
        $me = tap(User::make()->email('admin@domain.com')->makeSuper())->save();

        $this
            ->actingAs($me)
            ->postJson(cp_route('user.exists'), ['email' => 'existing@domain.com'])
            ->assertOk()
            ->assertExactJson(['exists' => true]);
    }
}
