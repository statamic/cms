<?php

namespace Tests\Feature\UserGroups;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateGroupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        UserGroup::all()->each->delete();
        Role::all()->each->delete();
        Role::make('one')->save();
        Role::make('two')->save();
        Role::make('three')->save();
    }

    private function update($group, $data = [])
    {
        $data = array_merge(
            ['title' => 'Test', 'handle' => 'test'],
            $data,
        );

        return $this->patch(cp_route('user-groups.update', $group->handle()), $data);
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

    #[Test]
    public function it_denies_access_without_permission_to_edit_groups()
    {
        $role = tap(Role::make('test'))->save();

        $this
            ->actingAsUserWithPermissions([])
            ->from('/original')
            ->update($role)
            ->assertRedirect('/original');
    }

    #[Test]
    public function it_updates_a_group()
    {
        $group = tap(
            UserGroup::make()
                ->handle('test')
                ->title('Test')
                ->roles(['one', 'two'])
        )->save();

        $this
            ->actingAsUserWithPermissions(['edit user groups'])
            ->update($group, [
                'title' => 'Updated',
                'handle' => 'changed',
            ])
            ->assertOk()
            ->assertJson(['title' => 'Updated']);

        $this->assertNull(UserGroup::find('test'));
        $group = UserGroup::find('changed');
        $this->assertEquals('Updated', $group->title());
        $this->assertEquals([
            'one' => Role::find('one'),
            'two' => Role::find('two'),
        ], $group->roles()->all());
    }

    #[Test]
    public function it_assigns_roles_with_permission()
    {
        $group = tap(
            UserGroup::make()
                ->handle('test')
                ->title('Test')
                ->roles(['one', 'two'])
        )->save();

        $this
            ->actingAsUserWithPermissions(['edit user groups', 'assign roles'])
            ->update($group, [
                'roles' => ['one', 'three'],
            ])
            ->assertOk()
            ->assertJson(['title' => 'Test']);

        $group = UserGroup::find('test');
        $this->assertEquals([
            'one' => Role::find('one'),
            'three' => Role::find('three'),
        ], $group->roles()->all());
    }

    #[Test]
    public function it_discards_roles_without_permission()
    {
        $group = tap(
            UserGroup::make()
                ->handle('test')
                ->title('Test')
                ->roles(['one', 'two'])
        )->save();

        $this
            ->actingAsUserWithPermissions(['edit user groups'])
            ->update($group, [
                'roles' => ['one', 'three'],
            ])
            ->assertOk()
            ->assertJson(['title' => 'Test']);

        $group = UserGroup::find('test');
        $this->assertEquals([
            'one' => Role::find('one'),
            'two' => Role::find('two'),
        ], $group->roles()->all());
    }
}
