<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Statamic\Auth\Eloquent\Role as EloquentRole;
use Statamic\Auth\Eloquent\RoleModel;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Console\Please\Kernel;
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

        $this->migrationsDir = __DIR__.'/__migrations__';

        $this->loadMigrationsFrom($this->migrationsDir);

        $tmpDir = $this->migrationsDir.'/tmp';

        if (! self::$migrationsGenerated) {
            $this->please('auth:migration', ['--path' => $tmpDir, '--test' => true]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);
    }

    public function tearDown(): void
    {
        // our down() migration sets password to not be nullable, so change it back
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(true)->change();
        });
    }

    private function please($command, $parameters = [])
    {
        return $this->app[Kernel::class]->call($command, $parameters);
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
                'remember_token' => str_random(10),
            ])
        );
    }

    /** @test */
    public function it_creates_a_role()
    {
        $role = $this->makeRole();

        $this->assertInstanceOf(EloquentRole::class, $role);
    }

    /** @test */
    public function it_assigns_a_role_to_a_user()
    {
        $role = $this->makeRole();
        $user = $this->makeUser();
        $user->assignRole($role);

        $this->assertEquals($user->roles()->first(), $role);
    }
}
