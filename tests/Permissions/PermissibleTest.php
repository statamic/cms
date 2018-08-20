<?php

namespace Tests\Permissions;

use Tests\TestCase;
use Statamic\Data\Users\User;
use Statamic\Permissions\Role;
use Statamic\API\Role as RoleAPI;
use Illuminate\Support\Collection;
use Statamic\Permissions\UserGroup;
use Statamic\API\UserGroup as UserGroupAPI;
use Statamic\Contracts\Permissions\Role as RoleContract;
use Statamic\Contracts\Permissions\UserGroup as UserGroupContract;

class PermissibleTest extends TestCase
{
    /** @test */
    function it_gets_and_assigns_roles()
    {
        $roleA = new class extends Role {
            public function handle(string $handle = null) { return 'a'; }
        };
        $roleB = new class extends Role {
            public function handle(string $handle = null) { return 'b'; }
        };
        $roleC = new class extends Role {
            public function handle(string $handle = null) { return 'c'; }
        };
        $roleD = new class extends Role {
            public function handle(string $handle = null) { return 'd'; }
        };

        RoleAPI::shouldReceive('find')->with('a')->andReturn($roleA);
        RoleAPI::shouldReceive('find')->with('b')->andReturn($roleB);
        RoleAPI::shouldReceive('find')->with('c')->andReturn($roleC);
        RoleAPI::shouldReceive('find')->with('d')->andReturn($roleD);

        $user = new User;
        $this->assertInstanceOf(Collection::class, $user->roles());
        $this->assertCount(0, $user->roles());

        $return = $user->assignRole([$roleA, 'b']);
        $return = $user->assignRole($roleC);
        $return = $user->assignRole('d');

        $this->assertInstanceOf(Collection::class, $user->roles());
        $this->assertCount(4, $user->roles());
        $this->assertEveryItemIsInstanceOf(RoleContract::class, $user->roles());
        $this->assertEquals([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
        ], $user->roles()->map->handle()->all());
        $this->assertEquals($user, $return);
    }

    /** @test */
    function it_removes_a_role_assignment()
    {
        $roleA = new class extends Role {
            public function handle(string $handle = null) { return 'a'; }
        };
        $roleB = new class extends Role {
            public function handle(string $handle = null) { return 'b'; }
        };
        $roleC = new class extends Role {
            public function handle(string $handle = null) { return 'c'; }
        };
        $roleD = new class extends Role {
            public function handle(string $handle = null) { return 'd'; }
        };

        RoleAPI::shouldReceive('find')->with('b')->andReturn($roleB);

        $user = (new User)->assignRole([$roleA, $roleB, $roleC, $roleD]);

        $return = $user->removeRole($roleA);
        $user->removeRole(['c', $roleD]);

        $this->assertEquals(['b' => $roleB], $user->roles()->all());
        $this->assertEquals($user, $return);
    }

    /** @test */
    function it_checks_if_it_has_a_role()
    {
        $roleA = new class extends Role {
            public function handle(string $handle = null) { return 'a'; }
        };
        $roleB = new class extends Role {
            public function handle(string $handle = null) { return 'b'; }
        };

        RoleAPI::shouldReceive('find')->with('a')->andReturn($roleA);

        $user = new User;
        $user->assignRole($roleA);

        $this->assertTrue($user->hasRole($roleA));
        $this->assertTrue($user->hasRole('a'));
        $this->assertFalse($user->hasRole($roleB));
        $this->assertFalse($user->hasRole('b'));
    }

    /** @test */
    function it_gets_and_checks_permissions()
    {
        $directRole = new class extends Role {
            public function handle(string $handle = null) { return 'direct'; }
            public function permissions(): Collection {
                return collect([
                    'permission one directly through role',
                    'permission two directly through role',
                ]);
            }
        };
        $userGroupRole = new class extends UserGroup {
            public function handle(string $handle = null) { return 'usergrouprole'; }
            public function permissions(): Collection {
                return collect([
                    'permission one through user group',
                    'permission two through user group',
                ]);
            }
        };
        $userGroup = (new UserGroup)->handle('usergroup')->assignRole($userGroupRole);

        RoleAPI::shouldReceive('find')->with('direct')->andReturn($directRole);
        UserGroupAPI::shouldReceive('find')->with('usergroup')->andReturn($userGroup);

        $user = (new User)
            ->assignRole($directRole)
            ->addToGroup($userGroup);

        $expectedPermissions = [
            'permission one directly through role',
            'permission two directly through role',
            'permission one through user group',
            'permission two through user group',
        ];
        $actualPermissions = $user->permissions()->all();
        sort($expectedPermissions);
        sort($actualPermissions);
        $this->assertEquals($expectedPermissions, $actualPermissions);

        $this->assertTrue($user->hasPermission('permission one directly through role'));
        $this->assertTrue($user->hasPermission('permission two directly through role'));
        $this->assertTrue($user->hasPermission('permission one through user group'));
        $this->assertTrue($user->hasPermission('permission two through user group'));
        $this->assertFalse($user->hasPermission('something else'));
    }

    /** @test */
    function it_checks_if_it_has_super_permissions_through_roles_and_groups()
    {
        $superRole = new class extends Role {
            public function handle(string $handle = null) { return 'superrole'; }
            public function isSuper(): bool { return true; }
        };
        $nonSuperRole = new class extends Role {
            public function handle(string $handle = null) { return 'nonsuperrole'; }
            public function isSuper(): bool { return false; }
        };

        $superGroup = (new UserGroup)->handle('supergroup')->assignRole($superRole);
        $nonSuperGroup = (new UserGroup)->handle('nonsupergroup')->assignRole($nonSuperRole);

        RoleAPI::shouldReceive('find')->with('superrole')->andReturn($superRole);
        RoleAPI::shouldReceive('find')->with('nonsuperrole')->andReturn($nonSuperRole);
        UserGroupAPI::shouldReceive('find')->with('supergroup')->andReturn($superGroup);
        UserGroupAPI::shouldReceive('find')->with('nonsupergroup')->andReturn($nonSuperGroup);

        $superUserThroughRole = (new User)->assignRole($superRole);
        $nonSuperUserThroughRole = (new User)->assignRole($nonSuperRole);
        $superUserThroughGroup = (new User)->addToGroup($superGroup);
        $nonSuperUserThroughGroup = (new User)->addToGroup($nonSuperGroup);

        $this->assertTrue($superUserThroughRole->isSuper());
        $this->assertFalse($nonSuperUserThroughRole->isSuper());
        $this->assertTrue($superUserThroughGroup->isSuper());
        $this->assertFalse($nonSuperUserThroughGroup->isSuper());
    }

    /** @test */
    function it_checks_if_it_has_super_permissions_on_itself()
    {
        $user = new User;
        $this->assertFalse($user->isSuper());
        $return = $user->makeSuper();
        $this->assertTrue($user->isSuper());
        $this->assertEquals($user, $return);
    }

    /** @test */
    function it_adds_to_groups()
    {
        $groupA = (new UserGroup)->handle('a');
        $groupB = (new UserGroup)->handle('b');
        $groupC = (new UserGroup)->handle('c');
        $user = new User;

        UserGroupAPI::shouldReceive('find')->with('a')->andReturn($groupA);
        UserGroupAPI::shouldReceive('find')->with('b')->andReturn($groupB);
        UserGroupAPI::shouldReceive('find')->with('c')->andReturn($groupC);

        $return = $user->addToGroup($groupA);
        $user->addToGroup([$groupB, $groupC]);

        $this->assertInstanceOf(Collection::class, $user->groups());
        $this->assertEveryItemIsInstanceOf(UserGroupContract::class, $user->groups());
        $this->assertEquals([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c'
        ], $user->groups()->map->handle()->all());
        $this->assertEquals($user, $return);

        $return = $user->removeFromGroup($groupB);

        $this->assertEquals([
            'a' => 'a',
            'c' => 'c'
        ], $user->groups()->map->handle()->all());
        $this->assertEquals($user, $return);

        $this->assertTrue($user->isInGroup($groupA));
        $this->assertFalse($user->isInGroup($groupB));
        $this->assertTrue($user->isInGroup($groupC));
    }

    /** @test */
    function it_sets_all_the_groups()
    {
        $groupA = (new UserGroup)->handle('a');
        $groupB = (new UserGroup)->handle('b');
        $groupC = (new UserGroup)->handle('c');
        $user = (new User)->addToGroup($groupA);

        UserGroupAPI::shouldReceive('find')->with('a')->andReturn($groupA);
        UserGroupAPI::shouldReceive('find')->with('b')->andReturn($groupB);
        UserGroupAPI::shouldReceive('find')->with('c')->andReturn($groupC);

        $user->groups([$groupB, $groupC]);

        $this->assertEquals([
            'b' => 'b',
            'c' => 'c'
        ], $user->groups()->map->handle()->all());
    }
}
