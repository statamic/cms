<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Eloquent\Role as EloquentRole;
use Statamic\Auth\Eloquent\RoleModel;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Support\Str;
use Tests\TestCase;

class EloquentRoleTest extends TestCase
{
    use WithFaker;

    public static $migrationsGenerated = false;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config([
            'statamic.users.repository' => 'eloquent',
            'statamic.users.tables.roles' => 'roles',
        ]);

        $this->loadMigrationsFrom(static::migrationsDir());

        $tmpDir = static::migrationsDir().'/tmp';

        if (! self::$migrationsGenerated) {
            $this->artisan('statamic:auth:migration', ['--path' => $tmpDir]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);
    }

    private static function migrationsDir()
    {
        return __DIR__.'/__migrations__';
    }

    public function tearDown(): void
    {
        \Statamic\Facades\User::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    public function makeRole()
    {
        return (new EloquentRole)
            ->model(RoleModel::create([
                'handle' => $this->faker->word,
                'title' => $this->faker->words(2, true),
                'permissions' => [],
                'preferences' => [],
            ])
            );
    }

    public function makeUser()
    {
        return (new EloquentUser)
            ->model(User::create([
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                // 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => Str::random(10),
            ])
            );
    }

    #[Test]
    public function it_creates_a_role()
    {
        $role = $this->makeRole();

        $this->assertInstanceOf(EloquentRole::class, $role);
    }

    #[Test]
    public function it_assigns_a_role_to_a_user()
    {
        $role = $this->makeRole();
        $user = $this->makeUser();
        $user->assignRole($role);

        $this->assertEquals($user->roles()->first(), $role);
    }

    #[Test]
    public function it_assigns_a_role_to_a_user_and_then_removes_it()
    {
        $role = $this->makeRole();
        $user = $this->makeUser();
        $user->assignRole($role);

        $this->assertEquals($user->roles()->first(), $role);
        $this->assertCount(1, $user->roles());

        $user->removeRole($role);

        $this->assertCount(0, $user->roles());
    }
}
