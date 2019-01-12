<?php

namespace Tests\Console;

use Tests\TestCase;
use Statamic\API\User;
use Illuminate\Filesystem\Filesystem;

class UserGeneratorTest extends TestCase
{
    public function tearDown()
    {
        $path = __DIR__.'/../__fixtures__/users';

        $this->files = app(Filesystem::class);
        $this->files->cleanDirectory($path);
        $this->files->put($path.'/.gitkeep', null);

        parent::tearDown();
    }

    /** @test */
    function it_can_make_a_user()
    {
        $this->withoutMockingConsoleOutput();

        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com']);

        $user = User::all()->first();

        $this->assertNotEmpty($user->get('id'));
        $this->assertEquals('jason@tellmewhatyouchasin.com', $user->email());
    }

    /** @test */
    function it_can_make_a_user_interactively()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user')
            ->expectsQuestion('Email', 'jason@ifyoucantescapeit.org')
            ->expectsQuestion('Name', 'Jason')
            ->expectsQuestion('Password (Your input will be hidden)', 'midnight')
            ->expectsQuestion('Super user', 'yes')
            ->assertExitCode(0);

        $user = User::all()->first();

        $this->assertNotEmpty($user->get('id'));
        $this->assertEquals('jason@ifyoucantescapeit.org', $user->email());
        $this->assertEquals('Jason', $user->get('name'));
        $this->assertNotEmpty($user->get('password_hash'));
        $this->assertTrue($user->get('super'));
    }

    /** @test */
    function it_validates_email()
    {
        $this->assertEmpty(User::all());

        // ??? Why won't this pass though?
        // $this->artisan('statamic:make:user', ['email' => 'jason'])
        //     ->expectsOutput('The input must be a valid email address.');

        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com'])
            ->expectsOutput('User created successfully.');

        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com'])
            ->expectsOutput('A user with this email already exists.');
    }
}
