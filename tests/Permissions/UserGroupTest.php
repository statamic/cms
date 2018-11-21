<?php

namespace Tests\Permissions;

use Tests\TestCase;
use Statamic\Data\Users\User;
use Statamic\Permissions\Role;
use Statamic\API\Role as RoleAPI;
use Statamic\API\User as UserAPI;
use Illuminate\Support\Collection;
use Statamic\Permissions\UserGroup;

class UserGroupTest extends TestCase
{
    /** @test */
    function it_gets_and_sets_the_title()
    {
        $group = new UserGroup;
        $this->assertNull($group->title());

        $return = $group->title('Test');

        $this->assertEquals('Test', $group->title());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_gets_and_sets_the_handle()
    {
        $group = new UserGroup;
        $this->assertNull($group->handle());

        $return = $group->handle('test');

        $this->assertEquals('test', $group->handle());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_gets_all_the_users()
    {
        $group = new UserGroup;
        $this->assertInstanceOf(Collection::class, $group->users());
        $this->assertCount(0, $group->users());

        $group->addUser($user = new User);

        $this->assertInstanceOf(Collection::class, $group->users());
        $this->assertEquals([$user], $group->users()->all());
    }

    /** @test */
    function it_adds_a_user()
    {
        $group = new UserGroup;

        $return = $group->addUser($user = new User);

        $this->assertEquals([$user], $group->users()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_adds_a_user_by_id()
    {
        $group = new UserGroup;

        UserAPI::shouldReceive('find')->with('123')->andReturn($user = new User);

        $return = $group->addUser('123');

        $this->assertEquals([$user], $group->users()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_removes_a_user()
    {
        $userA = new class extends User {
            public function id($id = null) { return '123'; }
        };
        $userB = new class extends User {
            public function id($id = null) { return '456'; }
        };

        $group = (new UserGroup)
            ->addUser($userA)
            ->addUser($userB);

        $return = $group->removeUser($userA);

        $this->assertCount(1, $group->users());
        $this->assertEquals(['456'], $group->users()->keys()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_removes_a_user_by_id()
    {
        $userA = new class extends User {
            public function id($id = null) { return '123'; }
        };
        $userB = new class extends User {
            public function id($id = null) { return '456'; }
        };

        $group = (new UserGroup)
            ->addUser($userA)
            ->addUser($userB);

        $return = $group->removeUser('123');

        $this->assertCount(1, $group->users());
        $this->assertEquals(['456'], $group->users()->keys()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_checks_if_a_user_is_in_the_group()
    {
        $userA = new class extends User {
            public function id($id = null) { return '123'; }
        };
        $userB = new class extends User {
            public function id($id = null) { return '456'; }
        };
        $group = (new UserGroup)->addUser($userA);

        $this->assertTrue($group->hasUser($userA));
        $this->assertFalse($group->hasUser($userB));
    }

    /** @test */
    function it_checks_if_a_user_is_in_the_group_by_id()
    {
        $userA = new class extends User {
            public function id($id = null) { return '123'; }
        };
        $userB = new class extends User {
            public function id($id = null) { return '456'; }
        };
        $group = (new UserGroup)->addUser($userA);

        $this->assertTrue($group->hasUser('123'));
        $this->assertFalse($group->hasUser('456'));
    }

    /** @test */
    function it_gets_and_sets_roles()
    {
        $group = new UserGroup;
        $this->assertInstanceOf(Collection::class, $group->roles());

        $role = new class extends Role {
            public function handle(string $handle = null) { return 'test'; }
        };
        $group->assignRole($role);

        $this->assertInstanceOf(Collection::class, $group->roles());
        $this->assertEveryItemIsInstanceOf(Role::class, $group->roles());
        $this->assertCount(1, $group->roles());
    }

    /** @test */
    function it_adds_a_role()
    {
        $role = new class extends Role {
            public function handle(string $handle = null) { return 'test'; }
        };
        $group = new UserGroup;

        $return = $group->assignRole($role);

        $this->assertEquals(['test' => 'test'], $group->roles()->map->handle()->all());
        $this->assertEquals($group, $return);
    }

    function it_adds_a_role_using_handle()
    {
        $role = new class extends Role {
            public function handle(string $handle = null) { return 'test'; }
        };
        RoleAPI::shouldReceive('find')->with('test')->andReturn($role);

        $group = new UserGroup;

        $return = $group->assignRole($role);

        $this->assertEquals(['test' => 'test'], $group->roles()->map->handle()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    function it_removes_a_role()
    {
        $role = new class extends Role {
            public function handle(string $handle = null) { return 'test'; }
        };

        $group = (new UserGroup)->assignRole($role);
        $this->assertCount(1, $group->roles());

        $return = $group->removeRole($role);

        $this->assertCount(0, $group->roles());
    }

    /** @test */
    function it_removes_a_role_by_handle()
    {
        $role = new class extends Role {
            public function handle(string $handle = null) { return 'test'; }
        };
        RoleAPI::shouldReceive('find')->with('test')->andReturn($role);

        $group = (new UserGroup)->assignRole($role);
        $this->assertCount(1, $group->roles());

        $return = $group->removeRole('test');

        $this->assertCount(0, $group->roles());
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

        $group = (new UserGroup)->assignRole($roleA);

        $this->assertTrue($group->hasRole($roleA));
        $this->assertFalse($group->hasRole($roleB));
    }

    /** @test */
    function it_checks_if_it_has_a_role_by_handle()
    {
        $roleA = new class extends Role {
            public function handle(string $handle = null) { return 'a'; }
        };
        $roleB = new class extends Role {
            public function handle(string $handle = null) { return 'b'; }
        };

        $group = (new UserGroup)->assignRole($roleA);

        $this->assertTrue($group->hasRole('a'));
        $this->assertFalse($group->hasRole('b'));
    }

    /** @test */
    function it_checks_if_it_has_permission()
    {
        $role = new class extends Role {
            public function permissions($permissions = null) {
                return collect(['one']);
            }
        };

        $group = (new UserGroup)->assignRole($role);

        $this->assertTrue($group->hasPermission('one'));
        $this->assertFalse($group->hasPermission('two'));
    }

    /** @test */
    function it_checks_if_it_has_super_permissions()
    {
        $superRole = new class extends Role {
            public function permissions($permissions = null) {
                return collect(['super']);
            }
        };
        $nonSuperRole = new class extends Role {
            public function permissions($permissions = null) {
                return collect(['test']);
            }
        };

        $superGroup = (new UserGroup)->assignRole($superRole);
        $nonSuperGroup = (new UserGroup)->assignRole($nonSuperRole);

        $this->assertTrue($superGroup->isSuper());
        $this->assertFalse($nonSuperGroup->isSuper());
    }

    /** @test */
    function it_can_be_saved()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_can_be_deleted()
    {
        $this->markTestIncomplete();
    }
}
