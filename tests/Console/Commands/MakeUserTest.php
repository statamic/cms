<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\User;
use Tests\TestCase;

class MakeUserTest extends TestCase
{
    private $files;

    public function tearDown(): void
    {
        $path = __DIR__.'/../../__fixtures__/users';

        $this->files = app(Filesystem::class);
        $this->files->cleanDirectory($path);
        $this->files->put($path.'/.gitkeep', null);

        parent::tearDown();
    }

    /** @test */
    public function it_can_make_a_user()
    {
        $this->withoutMockingConsoleOutput();

        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com']);

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('jason@tellmewhatyouchasin.com', $user->email());
    }

    /** @test */
    public function it_can_make_a_super_user_interactively()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user')
            ->expectsQuestion('Email', 'jason@ifyoucantescapeit.org')
            ->expectsQuestion('Name', 'Jason')
            ->expectsQuestion('Password', 'midnight')
            ->expectsQuestion('Super user?', true)
            ->assertExitCode(0);

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('jason@ifyoucantescapeit.org', $user->email());
        $this->assertEquals('Jason', $user->get('name'));
        $this->assertNotNull($user->password());
        $this->assertTrue($user->isSuper());
    }

    /** @test */
    public function it_can_make_a_non_super_user_interactively()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user')
            ->expectsQuestion('Email', 'jesses.girl@springfield.com')
            ->expectsQuestion('Name', 'Gertrude')
            ->expectsQuestion('Password', 'iloverickie')
            ->expectsQuestion('Super user?', false)
            ->assertExitCode(0);

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('jesses.girl@springfield.com', $user->email());
        $this->assertFalse($user->isSuper());
    }

    /** @test */
    public function it_validates_email()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user', ['email' => 'jason'])
            ->expectsOutputToContain(trans('validation.email', ['attribute' => 'input']));

        $this->artisan('statamic:make:user', ['email' => 'jason@keeponrunnin.com'])
            ->expectsOutputToContain('User created successfully.');

        $this->artisan('statamic:make:user', ['email' => 'jason@keeponrunnin.com'])
            ->expectsOutputToContain('A user with this email already exists.');
    }

    /** @test */
    public function it_generates_with_and_without_super_option()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user', ['email' => 'jason@keeponrunnin.com', '--super' => true]);
        $this->artisan('statamic:make:user', ['email' => 'jesses.girl@springfield.com']);

        $jason = User::all()->first();
        $girl = User::all()->last();

        $this->assertTrue($jason->isSuper());
        $this->assertFalse($girl->isSuper());
    }
}
