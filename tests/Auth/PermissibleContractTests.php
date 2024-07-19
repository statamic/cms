<?php

namespace Tests\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Facades\UserGroup as UserGroupAPI;

trait PermissibleContractTests
{
    abstract protected function createPermissible();

    #[Test]
    public function it_gets_and_assigns_roles()
    {
        // Prevent the anonymous role classes throwing errors when getting serialized
        // during event handling unrelated to this test.
        Event::fake();

        $roleA = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'b';
            }
        };
        $roleC = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'c';
            }
        };
        $roleD = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'd';
            }
        };

        RoleAPI::shouldReceive('find')->with('a')->andReturn($roleA);
        RoleAPI::shouldReceive('find')->with('b')->andReturn($roleB);
        RoleAPI::shouldReceive('find')->with('c')->andReturn($roleC);
        RoleAPI::shouldReceive('find')->with('d')->andReturn($roleD);
        RoleAPI::shouldReceive('find')->with('unknown')->andReturnNull();
        RoleAPI::shouldReceive('all')->andReturn(collect([$roleA, $roleB, $roleC, $roleD])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible();
        $this->assertInstanceOf(Collection::class, $user->roles());
        $this->assertCount(0, $user->roles());

        $return = $user->assignRole([$roleA, 'b']);
        $user->assignRole($roleC);
        $user->assignRole('d');
        $user->assignRole('unknown');
        $user->save();

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

    #[Test]
    public function it_removes_a_role_assignment()
    {
        // Prevent the anonymous role classes throwing errors when getting serialized
        // during event handling unrelated to this test.
        Event::fake();

        $roleA = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'b';
            }
        };
        $roleC = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'c';
            }
        };
        $roleD = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'd';
            }
        };

        RoleAPI::shouldReceive('find')->with('b')->andReturn($roleB);
        RoleAPI::shouldReceive('find')->with('c')->andReturn($roleC);
        RoleAPI::shouldReceive('all')->andReturn(collect([$roleA, $roleB, $roleC, $roleD])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible()->assignRole([$roleA, $roleB, $roleC, $roleD]);

        $return = $user->removeRole($roleA);
        $user->removeRole(['c', $roleD]);
        $user->save();

        $this->assertEquals(['b' => $roleB], $user->roles()->all());
        $this->assertEquals($user, $return);
    }

    #[Test]
    public function it_checks_if_it_has_a_role()
    {
        // Prevent the anonymous role classes throwing errors when getting serialized
        // during event handling unrelated to this test.
        Event::fake();

        $roleA = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'b';
            }
        };

        RoleAPI::shouldReceive('find')->with('a')->andReturn($roleA);
        RoleAPI::shouldReceive('all')->andReturn(collect([$roleA])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible();

        $this->assertFalse($user->hasRole($roleA));
        $this->assertFalse($user->hasRole('a'));
        $this->assertFalse($user->hasRole($roleB));
        $this->assertFalse($user->hasRole('b'));

        $user->assignRole($roleA);
        $user->save();

        $this->assertTrue($user->hasRole($roleA));
        $this->assertTrue($user->hasRole('a'));
        $this->assertFalse($user->hasRole($roleB));
        $this->assertFalse($user->hasRole('b'));
    }

    #[Test]
    public function it_gets_and_checks_permissions()
    {
        $directRole = RoleAPI::make('direct')->addPermission([
            'permission one directly through role',
            'permission two directly through role',
        ]);

        $userGroupRole = RoleAPI::make('usergrouprole')->addPermission([
            'permission one through user group',
            'permission two through user group',
        ]);

        $userGroup = (new UserGroup)->handle('usergroup')->assignRole($userGroupRole);

        RoleAPI::shouldReceive('find')->with('direct')->andReturn($directRole);
        RoleAPI::shouldReceive('all')->andReturn(collect([$directRole])); // the stache calls this when getting a user. unrelated to test.
        UserGroupAPI::shouldReceive('find')->with('usergroup')->andReturn($userGroup);
        RoleAPI::shouldReceive('all')->andReturn(collect([$directRole]));     // the stache calls this when getting a user. unrelated to test.
        UserGroupAPI::shouldReceive('all')->andReturn(collect([$userGroup])); // the stache calls this when getting a user. unrelated to test.

        $nonSuperUser = $this->createPermissible()
            ->assignRole($directRole)
            ->addToGroup($userGroup);
        $nonSuperUser->save();

        $superUser = $this->createPermissible()
            ->assignRole($directRole)
            ->addToGroup($userGroup)
            ->makeSuper();
        $superUser->save();

        $this->assertEquals([
            'permission one through user group',
            'permission two through user group',
            'permission one directly through role',
            'permission two directly through role',
        ], $nonSuperUser->permissions()->all());

        $this->assertEquals([
            'permission one through user group',
            'permission two through user group',
            'permission one directly through role',
            'permission two directly through role',
            'super',
        ], $superUser->permissions()->all());

        foreach ([$nonSuperUser, $superUser] as $user) {
            $this->assertTrue($user->hasPermission('permission one directly through role'));
            $this->assertTrue($user->hasPermission('permission two directly through role'));
            $this->assertTrue($user->hasPermission('permission one through user group'));
            $this->assertTrue($user->hasPermission('permission two through user group'));
            $this->assertFalse($user->hasPermission('something else'));
        }

        $directRole->addPermission('permission three directly through role');
        $userGroupRole->addPermission('permission three through user group');

        foreach ([$nonSuperUser, $superUser] as $user) {
            $this->assertTrue($user->hasPermission('permission three directly through role'));
            $this->assertTrue($user->hasPermission('permission three through user group'));
        }

        $this->assertFalse($nonSuperUser->hasPermission('super'));
        $this->assertTrue($superUser->hasPermission('super'));
    }

    #[Test]
    public function it_checks_if_it_has_super_permissions_through_roles_and_groups()
    {
        // Prevent the anonymous role classes throwing errors when getting serialized
        // during event handling unrelated to this test.
        Event::fake();

        $superRole = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'superrole';
            }

            public function permissions($permissions = null)
            {
                return ['super'];
            }
        };
        $nonSuperRole = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'nonsuperrole';
            }

            public function permissions($permissions = null)
            {
                return [];
            }
        };

        $superGroup = (new UserGroup)->handle('supergroup')->assignRole($superRole);
        $nonSuperGroup = (new UserGroup)->handle('nonsupergroup')->assignRole($nonSuperRole);

        RoleAPI::shouldReceive('find')->with('superrole')->andReturn($superRole);
        RoleAPI::shouldReceive('find')->with('nonsuperrole')->andReturn($nonSuperRole);
        RoleAPI::shouldReceive('all')->andReturn(collect([$superRole, $nonSuperRole])); // the stache calls this when getting a user. unrelated to test.
        UserGroupAPI::shouldReceive('find')->with('supergroup')->andReturn($superGroup);
        UserGroupAPI::shouldReceive('find')->with('nonsupergroup')->andReturn($nonSuperGroup);
        RoleAPI::shouldReceive('all')->andReturn(collect([$superRole, $nonSuperRole]));        // the stache calls this when getting a user. unrelated to test.
        UserGroupAPI::shouldReceive('all')->andReturn(collect([$superGroup, $nonSuperGroup])); // the stache calls this when getting a user. unrelated to test.

        $superUserThroughRole = $this->createPermissible()->assignRole($superRole)->save();
        $nonSuperUserThroughRole = $this->createPermissible()->assignRole($nonSuperRole)->save();
        $superUserThroughGroup = $this->createPermissible()->addToGroup($superGroup)->save();
        $nonSuperUserThroughGroup = $this->createPermissible()->addToGroup($nonSuperGroup)->save();

        $this->assertTrue($superUserThroughRole->isSuper());
        $this->assertFalse($nonSuperUserThroughRole->isSuper());
        $this->assertTrue($superUserThroughGroup->isSuper());
        $this->assertFalse($nonSuperUserThroughGroup->isSuper());
    }

    #[Test]
    public function it_checks_if_it_has_super_permissions_on_itself()
    {
        $user = $this->createPermissible()->save();
        $this->assertFalse($user->isSuper());
        $return = $user->makeSuper();
        $this->assertTrue($user->isSuper());
        $this->assertEquals($user, $return);
    }

    #[Test]
    public function it_adds_to_groups()
    {
        $groupA = (new UserGroup)->handle('a');
        $groupB = (new UserGroup)->handle('b');
        $groupC = (new UserGroup)->handle('c');
        $user = $this->createPermissible();

        $this->assertFalse($user->isInGroup($groupA));
        $this->assertFalse($user->isInGroup($groupB));
        $this->assertFalse($user->isInGroup($groupC));

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
            'c' => 'c',
        ], $user->groups()->map->handle()->all());
        $this->assertEquals($user, $return);

        $return = $user->removeFromGroup($groupB);

        $this->assertEquals([
            'a' => 'a',
            'c' => 'c',
        ], $user->groups()->map->handle()->all());
        $this->assertEquals($user, $return);

        $this->assertTrue($user->isInGroup($groupA));
        $this->assertFalse($user->isInGroup($groupB));
        $this->assertTrue($user->isInGroup($groupC));
    }

    #[Test]
    public function it_sets_all_the_groups()
    {
        $groupA = (new UserGroup)->handle('a');
        $groupB = (new UserGroup)->handle('b');
        $groupC = (new UserGroup)->handle('c');
        $user = $this->createPermissible()->addToGroup($groupA);

        UserGroupAPI::shouldReceive('find')->with('a')->andReturn($groupA);
        UserGroupAPI::shouldReceive('find')->with('b')->andReturn($groupB);
        UserGroupAPI::shouldReceive('find')->with('c')->andReturn($groupC);

        $user->groups([$groupB, $groupC]);

        $this->assertEquals([
            'b' => 'b',
            'c' => 'c',
        ], $user->groups()->map->handle()->all());
    }
}
