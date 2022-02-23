<?php

namespace Tests\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Statamic\Facades;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Facades\User as UserAPI;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserGroupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_and_sets_the_title()
    {
        $group = new UserGroup;
        $this->assertNull($group->title());

        $return = $group->title('Test');

        $this->assertEquals('Test', $group->title());
        $this->assertEquals($group, $return);
    }

    /** @test */
    public function it_gets_and_sets_the_handle()
    {
        $group = new UserGroup;
        $this->assertNull($group->handle());
        $this->assertNull($group->originalHandle());

        $return = $group->handle('test');

        $this->assertEquals('test', $group->handle());
        $this->assertEquals($group, $return);

        $group->handle('modified');

        $this->assertEquals('modified', $group->handle());
        $this->assertEquals('test', $group->originalHandle());
    }

    /** @test */
    public function it_gets_all_the_users()
    {
        config(['statamic.users.repositories.file.paths.groups' => __DIR__.'/../__fixtures__/dev-null/groups.yaml']);

        $userA = tap(UserAPI::make())->save();
        $userB = tap(UserAPI::make())->save();
        $group = tap((new UserGroup)->handle('test'))->save();

        $this->assertInstanceOf(Collection::class, $group->users());
        $this->assertCount(0, $group->users());
        $this->assertFalse($group->hasUser($userA));
        $this->assertFalse($group->hasUser($userB));

        $userA->addToGroup($group)->save();
        $this->assertCount(1, $group->users());
        $this->assertSame([$userA], $group->users()->all());
        $this->assertTrue($group->hasUser($userA));
        $this->assertFalse($group->hasUser($userB));

        $userB->addToGroup($group)->save();
        $this->assertCount(2, $group->users());
        $this->assertSame([$userA, $userB], $group->users()->all());
        $this->assertTrue($group->hasUser($userA));
        $this->assertTrue($group->hasUser($userB));
    }

    /** @test */
    public function it_gets_and_sets_roles()
    {
        $group = new UserGroup;
        $this->assertInstanceOf(Collection::class, $group->roles());

        $role = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'test';
            }
        };
        $group->assignRole($role);

        $this->assertInstanceOf(Collection::class, $group->roles());
        $this->assertEveryItemIsInstanceOf(Role::class, $group->roles());
        $this->assertCount(1, $group->roles());
    }

    /** @test */
    public function it_adds_a_role()
    {
        $role = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'test';
            }
        };
        $group = new UserGroup;

        $return = $group->assignRole($role);

        $this->assertEquals(['test' => 'test'], $group->roles()->map->handle()->all());
        $this->assertEquals($group, $return);
    }

    public function it_adds_a_role_using_handle()
    {
        $role = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'test';
            }
        };
        RoleAPI::shouldReceive('find')->with('test')->andReturn($role);

        $group = new UserGroup;

        $return = $group->assignRole($role);

        $this->assertEquals(['test' => 'test'], $group->roles()->map->handle()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    public function it_sets_all_roles()
    {
        RoleAPI::shouldReceive('find')->with('one')->andReturn($roleOne = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'one';
            }
        });
        RoleAPI::shouldReceive('find')->with('two')->andReturn($roleTwo = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'two';
            }
        });
        RoleAPI::shouldReceive('find')->with('three')->andReturn($roleThree = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'three';
            }
        });

        $group = new UserGroup;
        $group->assignRole('one');

        $return = $group->roles(['two', 'three']);

        $this->assertInstanceOf(Collection::class, $group->roles());
        $this->assertEquals(['two', 'three'], $group->roles()->map->handle()->values()->all());
        $this->assertEquals($group, $return);
    }

    /** @test */
    public function it_removes_a_role()
    {
        $role = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'test';
            }
        };

        $group = (new UserGroup)->assignRole($role);
        $this->assertCount(1, $group->roles());

        $return = $group->removeRole($role);

        $this->assertCount(0, $group->roles());
    }

    /** @test */
    public function it_removes_a_role_by_handle()
    {
        $role = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'test';
            }
        };
        RoleAPI::shouldReceive('find')->with('test')->andReturn($role);

        $group = (new UserGroup)->assignRole($role);
        $this->assertCount(1, $group->roles());

        $return = $group->removeRole('test');

        $this->assertCount(0, $group->roles());
    }

    /** @test */
    public function it_checks_if_it_has_a_role()
    {
        $roleA = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'b';
            }
        };

        $group = (new UserGroup)->assignRole($roleA);

        $this->assertTrue($group->hasRole($roleA));
        $this->assertFalse($group->hasRole($roleB));
    }

    /** @test */
    public function it_checks_if_it_has_a_role_by_handle()
    {
        $roleA = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role
        {
            public function handle(string $handle = null)
            {
                return 'b';
            }
        };

        $group = (new UserGroup)->assignRole($roleA);

        $this->assertTrue($group->hasRole('a'));
        $this->assertFalse($group->hasRole('b'));
    }

    /** @test */
    public function it_checks_if_it_has_permission()
    {
        $role = new class extends Role
        {
            public function permissions($permissions = null)
            {
                return collect(['one']);
            }
        };

        $group = (new UserGroup)->assignRole($role);

        $this->assertTrue($group->hasPermission('one'));
        $this->assertFalse($group->hasPermission('two'));
    }

    /** @test */
    public function it_checks_if_it_has_super_permissions()
    {
        $superRole = new class extends Role
        {
            public function permissions($permissions = null)
            {
                return collect(['super']);
            }
        };
        $nonSuperRole = new class extends Role
        {
            public function permissions($permissions = null)
            {
                return collect(['test']);
            }
        };

        $superGroup = (new UserGroup)->assignRole($superRole);
        $nonSuperGroup = (new UserGroup)->assignRole($nonSuperRole);

        $this->assertTrue($superGroup->isSuper());
        $this->assertFalse($nonSuperGroup->isSuper());
    }

    /** @test */
    public function it_can_be_saved()
    {
        $group = (new UserGroup);
        Facades\UserGroup::shouldReceive('save')->with($group)->once()->andReturnTrue();
        $this->assertTrue($group->save());
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $group = (new UserGroup);
        Facades\UserGroup::shouldReceive('delete')->with($group)->once()->andReturnTrue();
        $this->assertTrue($group->delete());
    }

    /** @test */
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $group = (new UserGroup)->handle('test')->title('Test');

        $group
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $group->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $group[$key]));
    }

    /** @test */
    public function it_is_arrayable()
    {
        $group = (new UserGroup)->handle('test')->title('Test');

        $this->assertInstanceOf(Arrayable::class, $group);

        collect($group->toArray())
            ->each(fn ($value, $key) => $this->assertEquals($value, $group->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value, $group[$key]));
    }
}
