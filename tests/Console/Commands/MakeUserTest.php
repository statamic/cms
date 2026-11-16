<?php

namespace Tests\Console\Commands;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\Auth\Eloquent\User as EloquentUser;
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

    #[Test]
    public function it_can_make_a_user()
    {
        $this->withoutMockingConsoleOutput();

        $this->assertEmpty(User::all());

        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com']);

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('jason@tellmewhatyouchasin.com', $user->email());
    }

    #[Test]
    public function it_can_make_an_eloquent_user_non_interactively()
    {
        config([
            'auth.providers.users.model' => EloquentUser::class,
            'statamic.users.repository' => 'eloquent',
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../../Auth/Eloquent/__migrations__');

        Schema::table('users', fn (Blueprint $table) => $table->string('name')->nullable()->change());

        collect(['role', 'group'])->each(fn ($type) => Schema::create("{$type}_user", function (Blueprint $table) use ($type) {
            $table->id();
            $table->foreignId('user_id');
            $table->string("{$type}_id");
        }));

        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com'])
            ->expectsOutputToContain('User created successfully.');

        $this->assertEquals('jason@tellmewhatyouchasin.com', User::all()->first()->email());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_can_make_a_user_with_password_option()
    {
        $this->assertEmpty(User::all());

        $password = 'PacManMoonwalk#84';

        $this->artisan('statamic:make:user', ['email' => 'duncan@likesteatime.com', '--password' => $password])
            ->expectsOutputToContain('User created successfully.');

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('duncan@likesteatime.com', $user->email());
        $this->assertNotEmpty($user->password());
        $this->assertTrue(password_verify($password, $user->password()));
    }

    #[Test]
    public function if_password_option_is_passed_it_will_not_prompt_for_password()
    {
        $this->assertEmpty(User::all());

        $password = 'PacManMoonwalk#84';

        $this->artisan('statamic:make:user', ['--password' => $password])
            ->expectsQuestion('Email', 'duncan@likesteatime.com')
            ->expectsQuestion('Name', 'Duncan')
            ->expectsQuestion('Super user?', false)
            ->assertExitCode(0);

        $user = User::all()->first();

        $this->assertNotEmpty($user->id());
        $this->assertEquals('duncan@likesteatime.com', $user->email());
        $this->assertNotEmpty($user->password());
        $this->assertTrue(password_verify($password, $user->password()));
    }

    #[Test]
    public function it_can_make_a_user_with_name_option()
    {
        $this->artisan('statamic:make:user', ['email' => 'jason@tellmewhatyouchasin.com', '--name' => 'Jason'])
            ->expectsOutputToContain('User created successfully.');

        $this->assertEquals('Jason', User::all()->first()->get('name'));
    }

    #[Test]
    public function it_can_make_a_user_with_first_and_last_name_options()
    {
        $this->artisan('statamic:make:user', [
            'email' => 'jason@tellmewhatyouchasin.com',
            '--first-name' => 'Jason',
            '--last-name' => 'Varga',
        ])->expectsOutputToContain('User created successfully.');

        $user = User::all()->first();

        $this->assertEquals('Jason', $user->get('first_name'));
        $this->assertEquals('Varga', $user->get('last_name'));
    }

    #[Test]
    public function if_name_option_is_passed_it_will_not_prompt_for_name()
    {
        $this->artisan('statamic:make:user', ['--name' => 'Duncan'])
            ->expectsQuestion('Email', 'duncan@likesteatime.com')
            ->expectsQuestion('Password', 'PacManMoonwalk#84')
            ->expectsQuestion('Super user?', false)
            ->assertExitCode(0);

        $this->assertEquals('Duncan', User::all()->first()->get('name'));
    }
}
