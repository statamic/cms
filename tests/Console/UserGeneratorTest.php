<?php

namespace Tests\Console;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\User;
use Tests\TestCase;

class UserGeneratorTest extends TestCase
{
    public function tearDown(): void
    {
        $path = __DIR__.'/../__fixtures__/users';

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
    public function it_can_make_a_user_interactively()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user')
            ->expectsQuestion('Email', 'jason@ifyoucantescapeit.org')
            ->expectsQuestion('Name', 'Jason')
            ->expectsQuestion('Password (Your input will be hidden)', 'midnight')
            ->expectsQuestion('Super user', 'yes')
            ->assertExitCode(0);

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('jason@ifyoucantescapeit.org', $user->email());
        $this->assertEquals('Jason', $user->get('name'));
        $this->assertNotNull($user->password());
        $this->assertTrue($user->isSuper());
    }

    /** @test */
    public function it_validates_email()
    {
        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user', ['email' => 'jason'])
            ->expectsOutput(trans('validation.email', ['attribute' => 'input']));

        $this->artisan('statamic:make:user', ['email' => 'jason@keeponrunnin.com'])
            ->expectsOutput('User created successfully.');

        $this->artisan('statamic:make:user', ['email' => 'jason@keeponrunnin.com'])
            ->expectsOutput('A user with this email already exists.');
    }
}
