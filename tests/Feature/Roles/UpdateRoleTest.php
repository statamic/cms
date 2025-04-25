<?php

namespace Tests\Feature\Roles;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class UpdateRoleTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        Role::all()->each->delete();
    }

    private function update($role, $data = [])
    {
        $data = array_merge(
            ['title' => 'Test', 'handle' => 'test'],
            $data,
        );

        return $this->patch(cp_route('roles.update', $role->handle()), $data);
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
        $user = $this->app['auth']->guard('web')->user();

        $this->session([
            "statamic_elevated_session_{$user->id}" => now()->timestamp,
        ]);

        return $this;
    }

    #[Test]
    public function it_denies_access_without_permission_to_edit_roles()
    {
        $role = tap(Role::make('test'))->save();

        $this
            ->actingAsUserWithPermissions([])
            ->withActiveElevatedSession()
            ->from('/original')
            ->update($role)
            ->assertRedirect('/original');
    }

    #[Test]
    public function it_denies_access_without_active_elevated_session()
    {
        $role = tap(Role::make('test'))->save();

        $this
            ->actingAsUserWithPermissions([])
            ->from('/original')
            ->update($role)
            ->assertForbidden();
    }

    #[Test]
    public function it_updates_a_role()
    {
        $role = tap(
            Role::make('test')
                ->title('Test')
                ->permissions(['one', 'two'])
        )->save();

        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->withActiveElevatedSession()
            ->update($role, [
                'title' => 'Updated',
                'handle' => 'changed',
                'permissions' => ['one', 'three'],
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $this->assertNull(Role::find('test'));
        $role = Role::find('changed');
        $this->assertEquals('Updated', $role->title());
        $this->assertEquals(['one', 'three'], $role->permissions()->all());
        $this->assertFalse($role->isSuper());
    }

    #[Test]
    public function super_users_can_mark_a_role_as_super()
    {
        $role = tap(
            Role::make('test')
                ->title('Test')
                ->permissions(['one', 'two'])
        )->save();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->withActiveElevatedSession()
            ->update($role, [
                'super' => true,
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test');
        $this->assertEquals(['super'], $role->permissions()->all());
        $this->assertTrue($role->isSuper());
    }

    #[Test]
    public function non_super_users_may_not_mark_a_role_as_super()
    {
        $role = tap(
            Role::make('test')
                ->title('Test')
                ->permissions(['one', 'two'])
        )->save();

        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->withActiveElevatedSession()
            ->update($role, [
                'super' => true,
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test');
        $this->assertEquals(['one', 'two'], $role->permissions()->all());
        $this->assertFalse($role->isSuper());
    }

    #[Test]
    public function cannot_sneak_a_super_into_permissions_array()
    {
        $role = tap(
            Role::make('test')
                ->title('Test')
                ->permissions(['one', 'two'])
        )->save();

        $this
            ->actingAsUserWithPermissions(['edit roles'])
            ->withActiveElevatedSession()
            ->update($role, [
                'super' => false,
                'permissions' => ['super'],
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('roles.index')]);

        $role = Role::find('test');
        $this->assertEquals(['one', 'two'], $role->permissions()->all());
        $this->assertFalse($role->isSuper());
    }
}
