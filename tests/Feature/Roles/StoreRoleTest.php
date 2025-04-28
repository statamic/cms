<?php

namespace Tests\Feature\Roles;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreRoleTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        Role::all()->each->delete();
    }

    private function store($data = [])
    {
        $data = array_merge(
            ['title' => 'Test', 'handle' => 'test'],
            $data,
        );

        return $this->post(cp_route('roles.store'), $data);
    }

    private function actingAsUserWithPermissions($permissions)
    {
        return $this->actingAs($this->userWithPermissions($permissions));
    }

    private function userWithPermissions($permissions)
    {
        Role::make('user')->permissions(['access cp', ...$permissions])->save();

        return tap(User::make()->assignRole('user'))->save();
    }

    private function withActiveElevatedSession()
    {
        return $this->session(['statamic_elevated_session' => now()->timestamp]);
    }

    #[Test]
    public function it_denies_access_without_permission_to_create_roles()
    {
        $this
            ->actingAsUserWithPermissions([])
            ->withActiveElevatedSession()
            ->from('/original')
            ->store()
            ->assertRedirect('/original');
    }

    #[Test]
    public function it_denies_access_without_active_elevated_session()
    {
        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->from('/original')
            ->store()
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function it_stores_a_role()
    {
        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->withActiveElevatedSession()
            ->store([
                'title' => 'Test',
                'handle' => 'test_role',
                'permissions' => ['one', 'two'],
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test_role');
        $this->assertEquals('Test', $role->title());
        $this->assertEquals(['one', 'two'], $role->permissions()->all());
        $this->assertFalse($role->isSuper());
    }

    #[Test]
    public function super_users_can_mark_a_role_as_super()
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->withActiveElevatedSession()
            ->store(['super' => true])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test');
        $this->assertEquals(['super'], $role->permissions()->all());
        $this->assertTrue($role->isSuper());
    }

    #[Test]
    public function non_super_users_may_not_mark_a_role_as_super()
    {
        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->withActiveElevatedSession()
            ->store(['super' => true])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test');
        $this->assertEquals([], $role->permissions()->all());
        $this->assertFalse($role->isSuper());
    }

    #[Test]
    public function cannot_sneak_a_super_into_permissions_array()
    {
        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->withActiveElevatedSession()
            ->store([
                'super' => false,
                'permissions' => ['super'],
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test');
        $this->assertEquals([], $role->permissions()->all());
        $this->assertFalse($role->isSuper());
    }
}
