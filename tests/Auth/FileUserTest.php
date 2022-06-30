<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Hash;
use Statamic\Auth\File\User;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades\Role;
use Statamic\Facades\User as UserFacade;
use Statamic\Facades\UserGroup;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group user */
class FileUserTest extends TestCase
{
    use UserContractTests, PermissibleContractTests, PreventSavingStacheItemsToDisk;

    public function makeUser()
    {
        return new User;
    }

    public function createPermissible()
    {
        return $this->makeUser();
    }

    /** @test */
    public function it_gets_path()
    {
        $this->assertEquals($this->fakeStacheDirectory.'/users/john@example.com.yaml', $this->user()->path());
    }

    /** @test */
    public function hashed_password_gets_added_as_the_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->data(['password_hash' => bcrypt('secret')]);

        $this->assertNotNull($user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertFalse($user->has('password_hash'));
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_gets_custom_computed_data()
    {
        UserFacade::computed('balance', function ($user) {
            return $user->name().'\'s balance is $25 owing.';
        });

        $user = $this->makeUser()->data(['name' => 'Han Solo']);

        $expectedData = [
            'name' => 'Han Solo',
        ];

        $expectedComputedData = [
            'balance' => 'Han Solo\'s balance is $25 owing.',
        ];

        $expectedValues = array_merge($expectedData, $expectedComputedData);

        $this->assertEquals($expectedData, $user->data()->all());
        $this->assertEquals($expectedComputedData, $user->computedData()->all());
        $this->assertEquals($expectedValues['name'], $user->value('name'));
        $this->assertEquals($expectedValues['balance'], $user->value('balance'));
    }

    /** @test */
    public function it_gets_empty_computed_data_by_default()
    {
        $this->assertEquals([], $this->user()->computedData()->all());
    }

    /** @test */
    public function it_can_use_actual_data_to_compose_computed_data()
    {
        UserFacade::computed('nickname', function ($user, $attribute) {
            return $attribute ?? 'Nameless';
        });

        $user = $this->makeUser();

        $this->assertEquals('Nameless', $user->value('nickname'));

        $user->data(['nickname' => 'The Hoff']);

        $this->assertEquals('The Hoff', $user->value('nickname'));
    }
}
