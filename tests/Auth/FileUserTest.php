<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\User;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades\Role;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Facades\UserGroup;
use Statamic\Facades\UserGroup as UserGroupAPI;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('user')]
class FileUserTest extends TestCase
{
    use PermissibleContractTests, PreventSavingStacheItemsToDisk, UserContractTests;

    public function makeUser()
    {
        return new User;
    }

    public function createPermissible()
    {
        return $this->makeUser();
    }

    #[Test]
    public function it_gets_path()
    {
        $this->assertEquals($this->fakeStacheDirectory.'/users/john@example.com.yaml', $this->user()->path());
    }

    #[Test]
    public function hashed_password_gets_added_as_the_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->data(['password_hash' => bcrypt('secret')]);

        $this->assertNotNull($user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertFalse($user->has('password_hash'));
    }

    #[Test]
    public function it_gets_file_contents_for_saving()
    {
        Hash::shouldReceive('make')->with('secret')->andReturn('hashed-secret');

        $user = $this->user()->password('secret');

        $this->assertEquals([
            'name' => 'John Smith',
            'foo' => 'bar',
            'roles' => [
                'role_one',
                'role_two',
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
            'id' => '123',
            'password_hash' => 'hashed-secret',
            'content' => 'Lorem Ipsum',
            'preferences' => [
                'locale' => 'en',
            ],
        ], Arr::removeNullValues($user->fileData()));
    }

    #[Test]
    public function it_gets_permissions_from_a_cache()
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
        $userGroupRole->shouldReceive('permissions')->twice()->andReturn(collect([
            'permission one through user group',
            'permission two through user group',
        ]));

        $userGroup->shouldReceive('id')->andReturn('usergroup');
        $userGroup->shouldReceive('handle')->andReturn('usergroup');
        $userGroup->shouldReceive('roles')->once()->andReturn(collect([$userGroupRole]))->times(4);

        Role::shouldReceive('find')->with('direct')->andReturn($directRole);
        Role::shouldReceive('all')->andReturn(collect([$directRole])); // the stache calls this when getting a user. unrelated to test.
        UserGroup::shouldReceive('find')->with('usergroup')->andReturn($userGroup);
        Role::shouldReceive('all')->andReturn(collect([$directRole]));     // the stache calls this when getting a user. unrelated to test.
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

    #[Test]
    public function it_prevents_saving_duplicate_roles()
    {
        $roleA = (new \Statamic\Auth\File\Role)->handle('a');
        $roleB = (new \Statamic\Auth\File\Role)->handle('b');
        $roleC = (new \Statamic\Auth\File\Role)->handle('c');

        RoleAPI::shouldReceive('find')->with('a')->andReturn($roleA);
        RoleAPI::shouldReceive('find')->with('b')->andReturn($roleB);
        RoleAPI::shouldReceive('find')->with('c')->andReturn($roleC);
        RoleAPI::shouldReceive('all')->andReturn(collect([$roleA, $roleB])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible();
        $user->assignRole('a');

        $this->assertEquals(['a'], $user->get('roles'));

        $user->assignRole(['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $user->get('roles'));
    }

    #[Test]
    public function it_prevents_saving_duplicate_groups()
    {
        $groupA = (new \Statamic\Auth\File\UserGroup)->handle('a');
        $groupB = (new \Statamic\Auth\File\UserGroup)->handle('b');
        $groupC = (new \Statamic\Auth\File\UserGroup)->handle('c');

        UserGroupAPI::shouldReceive('find')->with('a')->andReturn($groupA);
        UserGroupAPI::shouldReceive('find')->with('b')->andReturn($groupB);
        UserGroupAPI::shouldReceive('find')->with('c')->andReturn($groupC);

        $user = $this->createPermissible();
        $user->addToGroup('a');

        $this->assertEquals(['a'], $user->get('groups'));

        $user->addToGroup(['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $user->get('groups'));
    }

    #[Test]
    public function it_clones_internal_collections()
    {
        $user = $this->user();
        $user->set('foo', 'A');
        $user->setSupplement('bar', 'A');

        $clone = clone $user;
        $clone->set('foo', 'B');
        $clone->setSupplement('bar', 'B');

        $this->assertEquals('A', $user->get('foo'));
        $this->assertEquals('B', $clone->get('foo'));

        $this->assertEquals('A', $user->getSupplement('bar'));
        $this->assertEquals('B', $clone->getSupplement('bar'));
    }
}
