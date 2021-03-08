<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Hash;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Statamic\Facades;

trait UserContractTests
{
    abstract public function makeUser();

    public function user()
    {
        return $this->makeUser()
            ->id(123)
            ->email('john@example.com')
            ->data([
                'name' => 'John Smith',
                'foo' => 'bar',
                'content' => 'Lorem Ipsum',
            ])
            ->setSupplement('supplemented', 'qux')
            ->assignRole($this->createRole('role_one'))
            ->assignRole($this->createRole('role_two'))
            ->addToGroup($this->createGroup('group_one'))
            ->addToGroup($this->createGroup('group_two'));
    }

    /** @test */
    public function it_gets_email()
    {
        $this->assertEquals('john@example.com', $this->user()->email());
    }

    /** @test */
    public function it_gets_email_as_property()
    {
        $this->assertEquals('john@example.com', $this->user()->email);
    }

    /** @test */
    public function gets_the_name()
    {
        $this->assertEquals('John', $this->makeUser()->set('name', 'John')->name());
        $this->assertEquals('John Smith', $this->makeUser()->set('name', 'John Smith')->name());
        $this->assertEquals('John', $this->makeUser()->data(['name' => null, 'first_name' => 'John'])->name());
        $this->assertEquals('John Smith', $this->makeUser()->data(['name' => null, 'first_name' => 'John', 'last_name' => 'Smith'])->name());
        $this->assertEquals('john@example.com', $this->makeUser()->remove('name')->email('john@example.com')->name());
    }

    /** @test */
    public function it_gets_data()
    {
        $this->assertEquals(array_merge([
            'name' => 'John Smith',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'roles' => [
                'role_one',
                'role_two',
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
        ], $this->additionalDataValues()), $this->user()->data()->all());
    }

    public function additionalDataValues()
    {
        return [];
    }

    /** @test */
    public function it_gets_id()
    {
        $this->assertEquals('123', $this->user()->id());
    }

    /** @test */
    public function it_gets_initials_from_name()
    {
        $this->assertEquals('JS', $this->user()->initials());
    }

    /** @test */
    public function it_gets_initials_from_name_with_no_surname()
    {
        $this->assertEquals('J', $this->user()->set('name', 'John')->initials());
    }

    /** @test */
    public function it_gets_initials_from_email_if_name_doesnt_exist()
    {
        $user = $this->user()->remove('name');

        $this->assertEquals('J', $user->initials());
    }

    /** @test */
    public function it_gets_avatar_from_gravatar_if_config_allows()
    {
        config(['statamic.users.avatars' => 'gravatar']);

        $this->assertEquals(
            'https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64',
            $this->user()->avatar()
        );
        $this->assertEquals(
            'https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=32',
            $this->user()->avatar(32)
        );

        config(['statamic.users.avatars' => 'initials']);

        $this->assertNull($this->user()->avatar());
    }

    /** @test */
    public function it_encrypts_a_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->password('secret');

        $this->assertNotNull($user->password());
        $this->assertNotEquals('secret', $user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
    }

    /** @test */
    public function it_encrypts_a_password_when_set_through_data()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->data([
            'foo' => 'bar',
            'password' => 'secret',
        ]);

        $this->assertNotNull($user->password());
        $this->assertNotEquals('secret', $user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertArrayNotHasKey('password', $user->data());
    }

    /** @test */
    public function converts_to_array()
    {
        Role::shouldReceive('all')->andReturn(collect([
            $this->createRole('role_one'),
            $this->createRole('role_two'),
            $this->createRole('role_three'),
        ]));

        UserGroup::shouldReceive('all')->andReturn(collect([
            $this->createGroup('group_one'),
            $this->createGroup('group_two'),
            $this->createGroup('group_three'),
        ]));

        $arr = $this->user()->toAugmentedArray();

        $arr = array_map(function ($item) {
            return $item instanceof \Statamic\Fields\Value ? $item->value() : $item;
        }, $arr);

        $this->assertEquals(array_merge([
            'name' => 'John Smith',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'email' => 'john@example.com',
            'id' => 123,
            'roles' => [
                'role_one',
                'role_two',
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
            'is_role_one' => true,
            'is_role_two' => true,
            'is_role_three' => false,
            'in_group_one' => true,
            'in_group_two' => true,
            'in_group_three' => false,
            'supplemented' => 'qux',
            'avatar' => null,
            'initials' => 'JS',
            'is_user' => true,
            'title' => 'john@example.com',
            'edit_url' => 'http://localhost/cp/users/123/edit',
            'last_login' => null,
            'api_url' => 'http://localhost/api/users/123',
        ], $this->additionalToArrayValues()), $arr);
    }

    public function additionalToArrayValues()
    {
        return [];
    }

    private function createRole($handle)
    {
        $class = new class($handle) extends Role {
            public function __construct($handle)
            {
                $this->handle = $handle;
            }
        };

        Facades\Role::shouldReceive('find')
            ->with($handle)
            ->andReturn($class);

        return $class;
    }

    private function createGroup($handle)
    {
        $class = new class($handle) extends UserGroup {
            public function __construct($handle)
            {
                $this->handle = $handle;
            }
        };

        Facades\UserGroup::shouldReceive('find')
            ->with($handle)
            ->andReturn($class);

        return $class;
    }
}
