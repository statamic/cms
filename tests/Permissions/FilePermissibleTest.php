<?php

namespace Tests\Permissions;

use Statamic\Auth\File\User;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades\Role;
use Statamic\Facades\UserGroup;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FilePermissibleTest extends TestCase
{
    use PermissibleContractTests;
    use PreventSavingStacheItemsToDisk;

    protected function createPermissible()
    {
        return new User;
    }

    /** @test */
    function it_gets_permissions_from_a_cache()
    {
        $directRole = $this->mock(RoleContract::class);
        $userGroupRole = $this->mock(RoleContract::class);
        $userGroup = $this->mock(UserGroupContract::class);

        $directRole->shouldReceive('id')->andReturn('direct');
        $directRole->shouldReceive('handle')->andReturn('direct');
        $directRole->shouldReceive('permissions')->once()->andReturn(collect([
            'permission one directly through role',
            'permission two directly through role',
        ]));

        $userGroupRole->shouldReceive('id')->andReturn('usergrouprole');
        $userGroupRole->shouldReceive('handle')->andReturn('usergrouprole');
        $userGroupRole->shouldReceive('permissions')->once()->andReturn(collect([
            'permission one through user group',
            'permission two through user group',
        ]));

        $userGroup->shouldReceive('id')->andReturn('usergroup');
        $userGroup->shouldReceive('handle')->andReturn('usergroup');
        $userGroup->shouldReceive('roles')->once()->andReturn(collect([$userGroupRole]));

        Role::shouldReceive('find')->with('direct')->andReturn($directRole);
        UserGroup::shouldReceive('find')->with('usergroup')->andReturn($userGroup);
        UserGroup::shouldReceive('all')->andReturn(collect([$userGroup])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible()
            ->assignRole($directRole)
            ->addToGroup($userGroup);
        $user->save();

        $expectedPermissions = [
            'permission one through user group',
            'permission two through user group',
            'permission one directly through role',
            'permission two directly through role',
        ];
        $this->assertEquals($expectedPermissions, $user->permissions()->all());

        // Doing it a second time should give the same result but without multiple calls.
        $this->assertEquals($expectedPermissions, $user->permissions()->all());
    }
}
