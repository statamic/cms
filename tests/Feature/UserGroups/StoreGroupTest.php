<?php

namespace Tests\Feature\UserGroups;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreGroupTest extends TestCase
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

    private function store($data = [])
    {
        $data = array_merge(
            ['title' => 'Test', 'handle' => 'test'],
            $data,
        );

        return $this->post(cp_route('user-groups.store'), $data);
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
        $this
            ->actingAsUserWithPermissions([])
            ->from('/original')
            ->store()
            ->assertRedirect('/original');
    }

    #[Test]
    public function it_stores_a_group()
    {
        $this
            ->actingAsUserWithPermissions(['edit user groups'])
            ->store([
                'title' => 'Test',
                'handle' => 'test_role',
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('user-groups.show', 'test_role')]);

        $group = UserGroup::find('test_role');
        $this->assertEquals('Test', $group->title());
        $this->assertEquals([], $group->roles()->all());
    }

    #[Test]
    public function it_assigns_roles_with_permission()
    {
        $this
            ->actingAsUserWithPermissions(['edit user groups', 'assign roles'])
            ->store([
                'roles' => ['one', 'three'],
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('user-groups.show', 'test')]);

        $group = UserGroup::find('test');
        $this->assertEquals([
            'one' => Role::find('one'),
            'three' => Role::find('three'),
        ], $group->roles()->all());
    }

    #[Test]
    public function it_discards_roles_without_permission()
    {
        $this
            ->actingAsUserWithPermissions(['edit user groups'])
            ->store([
                'roles' => ['one', 'three'],
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('user-groups.show', 'test')]);

        $group = UserGroup::find('test');
        $this->assertEquals([], $group->roles()->all());
    }
}
