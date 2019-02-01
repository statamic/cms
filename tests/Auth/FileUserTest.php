<?php

namespace Tests\Auth;

use Tests\TestCase;
use Statamic\Auth\File\User;
use Illuminate\Support\Facades\Hash;

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
        $dir = realpath(__DIR__.'/../') . '/__fixtures__/users';

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

        $expected = <<<'EOT'
---
name: 'John Smith'
foo: bar
roles:
  - role_one
  - role_two
groups:
  - group_one
  - group_two
id: '123'
password_hash: hashed-secret
---
Lorem Ipsum
EOT;

        $this->assertEquals($expected, $user->fileContents());
    }
}
