<?php

namespace Tests\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Role;
use Statamic\Facades;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Facades\User as UserAPI;
use Statamic\Facades\UserGroup;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserGroupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_and_sets_the_title()
    {
        $group = UserGroup::make();
        $group->handle('testing');
        $this->assertEquals('Testing', $group->title());

        $return = $group->title('Test');

        $this->assertEquals('Test', $group->title());
        $this->assertEquals($group, $return);
    }

    #[Test]
    public function it_gets_and_sets_the_handle()
    {
        $group = UserGroup::make();
        $this->assertNull($group->handle());
        $this->assertNull($group->originalHandle());

        $return = $group->handle('test');

        $this->assertEquals('test', $group->handle());
        $this->assertEquals($group, $return);

        $group->handle('modified');

        $this->assertEquals('modified', $group->handle());
        $this->assertEquals('test', $group->originalHandle());
    }

    #[Test]
    public function it_gets_all_the_users()
    {
        config(['statamic.users.repositories.file.paths.groups' => __DIR__.'/../__fixtures__/dev-null/groups.yaml']);

        $userA = tap(UserAPI::make())->save();
        $userB = tap(UserAPI::make())->save();
        $group = tap(UserGroup::make()->handle('test'))->save();

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

    #[Test]
    public function it_gets_and_sets_roles()
    {
        $group = UserGroup::make();
        $this->assertInstanceOf(Collection::class, $group->roles());

        $role = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'test';
            }
        };
        $group->assignRole($role);

        $this->assertInstanceOf(Collection::class, $group->roles());
        $this->assertEveryItemIsInstanceOf(Role::class, $group->roles());
        $this->assertCount(1, $group->roles());
    }

    #[Test]
    public function it_adds_a_role()
    {
        $role = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'test';
            }
        };
        $group = UserGroup::make();

        $return = $group->assignRole($role);

        $this->assertEquals(['test' => 'test'], $group->roles()->map->handle()->all());
        $this->assertEquals($group, $return);
    }

    public function it_adds_a_role_using_handle()
    {
        $role = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'test';
            }
        };
        RoleAPI::shouldReceive('find')->with('test')->andReturn($role);

        $group = UserGroup::make();

        $return = $group->assignRole($role);

        $this->assertEquals(['test' => 'test'], $group->roles()->map->handle()->all());
        $this->assertEquals($group, $return);
    }

    #[Test]
    public function it_sets_all_roles()
    {
        RoleAPI::shouldReceive('find')->with('one')->andReturn($roleOne = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'one';
            }
        });
        RoleAPI::shouldReceive('find')->with('two')->andReturn($roleTwo = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'two';
            }
        });
        RoleAPI::shouldReceive('find')->with('three')->andReturn($roleThree = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'three';
            }
        });

        $group = UserGroup::make();
        $group->assignRole('one');

        $return = $group->roles(['two', 'three']);

        $this->assertInstanceOf(Collection::class, $group->roles());
        $this->assertEquals(['two', 'three'], $group->roles()->map->handle()->values()->all());
        $this->assertEquals($group, $return);
    }

    #[Test]
    public function it_removes_a_role()
    {
        $role = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'test';
            }
        };

        $group = UserGroup::make()->assignRole($role);
        $this->assertCount(1, $group->roles());

        $return = $group->removeRole($role);

        $this->assertCount(0, $group->roles());
    }

    #[Test]
    public function it_removes_a_role_by_handle()
    {
        $role = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'test';
            }
        };
        RoleAPI::shouldReceive('find')->with('test')->andReturn($role);

        $group = UserGroup::make()->assignRole($role);
        $this->assertCount(1, $group->roles());

        $return = $group->removeRole('test');

        $this->assertCount(0, $group->roles());
    }

    #[Test]
    public function it_checks_if_it_has_a_role()
    {
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

        $group = UserGroup::make()->assignRole($roleA);

        $this->assertTrue($group->hasRole($roleA));
        $this->assertFalse($group->hasRole($roleB));
    }

    #[Test]
    public function it_checks_if_it_has_a_role_by_handle()
    {
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

        $group = UserGroup::make()->assignRole($roleA);

        $this->assertTrue($group->hasRole('a'));
        $this->assertFalse($group->hasRole('b'));
    }

    #[Test]
    public function it_checks_if_it_has_permission()
    {
        $role = new class extends Role
        {
            public function permissions($permissions = null)
            {
                return collect(['one']);
            }
        };

        $group = UserGroup::make()->assignRole($role);

        $this->assertTrue($group->hasPermission('one'));
        $this->assertFalse($group->hasPermission('two'));
    }

    #[Test]
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

        $superGroup = UserGroup::make()->assignRole($superRole);
        $nonSuperGroup = UserGroup::make()->assignRole($nonSuperRole);

        $this->assertTrue($superGroup->isSuper());
        $this->assertFalse($nonSuperGroup->isSuper());
    }

    #[Test]
    public function it_can_be_saved()
    {
        $group = UserGroup::make();
        Facades\UserGroup::shouldReceive('save')->with($group)->once()->andReturnTrue();
        $this->assertTrue($group->save());
    }

    #[Test]
    public function it_can_be_deleted()
    {
        $group = UserGroup::make();
        Facades\UserGroup::shouldReceive('delete')->with($group)->once()->andReturnTrue();
        $this->assertTrue($group->delete());
    }

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $group = UserGroup::make()->handle('test')->title('Test');

        $group
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $group->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $group[$key]));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $group = UserGroup::make()->handle('test')->title('Test');

        $this->assertInstanceOf(Arrayable::class, $group);

        collect($group->toArray())
            ->each(fn ($value, $key) => $this->assertEquals($value, $group->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value, $group[$key]));
    }

    #[Test]
    public function it_gets_data()
    {
        $group = UserGroup::make()->handle('test')->data([
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
        ], $group->data()->all());
    }

    #[Test]
    public function it_gets_blueprint_values()
    {
        $blueprint = Facades\UserGroup::blueprint();
        $contents = $blueprint->contents();
        $contents['tabs']['main']['sections'][0]['fields'] = array_merge($contents['tabs']['main']['sections'][0]['fields'], [
            ['handle' => 'two', 'field' => ['type' => 'text']],
            ['handle' => 'four', 'field' => ['type' => 'text']],
            ['handle' => 'unused_in_bp', 'field' => ['type' => 'text']],
        ]);
        $blueprint->setContents($contents);
        Facades\Blueprint::shouldReceive('find')->with('user_group')->andReturn($blueprint);

        $data = [
            'one' => 'the "one" value on the group',
            'two' => 'the "two" value on the group and in the blueprint',
        ];

        $group = UserGroup::make()
            ->handle('group_1')
            ->data($data);

        $this->assertEquals($group->get('one'), $data['one']);
        $this->assertEquals($group->get('two'), $data['two']);
    }

    #[Test]
    public function it_clones_internal_collections()
    {
        $group = UserGroup::make();
        $group->set('foo', 'A');
        $group->setSupplement('bar', 'A');

        $clone = clone $group;
        $clone->set('foo', 'B');
        $clone->setSupplement('bar', 'B');

        $this->assertEquals('A', $group->get('foo'));
        $this->assertEquals('B', $clone->get('foo'));

        $this->assertEquals('A', $group->getSupplement('bar'));
        $this->assertEquals('B', $clone->getSupplement('bar'));
    }
}
