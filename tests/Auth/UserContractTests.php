<?php

namespace Tests\Auth;

use Statamic\API;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Illuminate\Support\Facades\Hash;

trait UserContractTests
{
    abstract function makeUser();

    function user()
    {
        return $this->makeUser()
            ->id('123')
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
    function it_gets_email()
    {
        $this->assertEquals('john@example.com', $this->user()->email());
    }

    /** @test */
    function it_gets_data()
    {
        $this->assertEquals([
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
            ]
        ], $this->user()->data());
    }

    /** @test */
    function it_gets_id()
    {
        $this->assertEquals('123', $this->user()->id());
    }

    /** @test */
    function it_gets_initials_from_name()
    {
        $this->assertEquals('JS', $this->user()->initials());
    }

    /** @test */
    function it_gets_initials_from_name_with_no_surname()
    {
        $this->assertEquals('J', $this->user()->set('name', 'John')->initials());
    }

    /** @test */
    function it_gets_initials_from_email_if_name_doesnt_exist()
    {
        $user = $this->user()->remove('name');

        $this->assertEquals('J', $user->initials());
    }

    /** @test */
    function it_gets_avatar_from_gravatar_if_config_allows()
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
    function it_encrypts_a_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->password('secret');

        $this->assertNotNull($user->password());
        $this->assertNotEquals('secret', $user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
    }

    /** @test */
    function it_encrypts_a_password_when_set_through_data()
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
    function converts_to_array()
    {
        $this->assertEquals([
            'name' => 'John Smith',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'email' => 'john@example.com',
            'id' => 123,
            'roles' => [
                'role_one',
                'role_two'
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
            'is_role_one' => true,
            'is_role_two' => true,
            'in_group_one' => true,
            'in_group_two' => true,
            'preferences' => [],
            'supplemented' => 'qux'
        ], $this->user()->toArray());
    }

    private function createRole($handle)
    {
        $class = new class($handle) extends Role {
            public function __construct($handle) { $this->handle = $handle; }
        };

        API\Role::shouldReceive('find')
            ->with($handle)
            ->andReturn($class);

        return $class;
    }

    private function createGroup($handle)
    {
        $class = new class($handle) extends UserGroup {
            public function __construct($handle) { $this->handle = $handle; }
        };

        API\UserGroup::shouldReceive('find')
            ->with($handle)
            ->andReturn($class);

        return $class;
    }
}
