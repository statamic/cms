<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Hash;
use Statamic\Auth\File\User;
use Statamic\Facades\Path;
use Statamic\Support\Arr;
use Tests\TestCase;

/** @group user */
class FileUserTest extends TestCase
{
    use UserContractTests;

    function makeUser() {
        return new User;
    }

    /** @test */
    function it_gets_path()
    {
        $dir = Path::tidy(realpath(__DIR__.'/../') . '/__fixtures__/users');

        $this->assertEquals($dir . '/john@example.com.yaml', $this->user()->path());
    }

    /** @test */
    function hashed_password_gets_added_as_the_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->data(['password_hash' => bcrypt('secret')]);

        $this->assertNotNull($user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertFalse($user->has('password_hash'));
    }

    /** @test */
    function it_gets_file_contents_for_saving()
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
        ], Arr::removeNullValues($user->fileData()));
    }
}
