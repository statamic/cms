<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\File\Role;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Facades;
use Tests\Auth\PermissibleContractTests;
use Tests\Auth\UserContractTests;
use Tests\Preferences\HasPreferencesTests;
use Tests\TestCase;

class EloquentUserTest extends TestCase
{
    use HasPreferencesTests, PermissibleContractTests, UserContractTests, WithFaker;

    public static $migrationsGenerated = false;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config(['statamic.users.repository' => 'eloquent']);

        $this->migrationsDir = __DIR__.'/__migrations__';

        $this->loadMigrationsFrom($this->migrationsDir);

        $tmpDir = $this->migrationsDir.'/tmp';

        if (! self::$migrationsGenerated) {
            $this->artisan('statamic:auth:migration', ['--path' => $tmpDir]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);

        // Prevent the anonymous role classes throwing errors when getting serialized
        // during event handling unrelated to these tests.
        Event::fake();
    }

    public function tearDown(): void
    {
        // Prevent error about null password during the down migration.
        User::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(__DIR__.'/__migrations__/tmp');

        parent::tearDownAfterClass();
    }

    /** @test */
    public function it_gets_roles_already_in_the_db_without_explicitly_assigning_them()
    {
        $roleA = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'b';
            }
        };
        $roleC = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'c';
            }
        };
        $roleD = new class extends Role
        {
            public function handle(?string $handle = null)
            {
                return 'd';
            }
        };

        Facades\Role::shouldReceive('find')->with('a')->andReturn($roleA);
        Facades\Role::shouldReceive('find')->with('b')->andReturn($roleB);
        Facades\Role::shouldReceive('find')->with('c')->andReturn($roleC);
        Facades\Role::shouldReceive('find')->with('d')->andReturn($roleD);
        Facades\Role::shouldReceive('find')->with('unknown')->andReturnNull();

        $user = $this->createPermissible();

        \DB::table(config('statamic.users.tables.role_user', 'role_user'))->insert([
            ['user_id' => $user->id(), 'role_id' => 'a'],
            ['user_id' => $user->id(), 'role_id' => 'b'],
            ['user_id' => $user->id(), 'role_id' => 'c'],
            ['user_id' => $user->id(), 'role_id' => 'd'],
        ]);

        $this->assertInstanceOf(Collection::class, $user->roles());
        $this->assertCount(4, $user->roles());
        $this->assertEveryItemIsInstanceOf(RoleContract::class, $user->roles());
        $this->assertEquals([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
        ], $user->roles()->map->handle()->all());
    }

    public function makeUser()
    {
        return (new EloquentUser)
            ->model(User::create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                // 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => str_random(10),
            ])
            );
    }

    public function createPermissible()
    {
        return $this->makeUser();
    }

    public function additionalToArrayValues()
    {
        return [
            'created_at' => Carbon::parse('2019-11-21 23:39:29'),
            'updated_at' => Carbon::parse('2019-11-21 23:39:29'),
            'preferences' => [
                'locale' => 'en',
            ],
        ];
    }

    public function additionalDataValues()
    {
        return [
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'preferences' => [
                'locale' => 'en',
            ],
        ];
    }

    /** @test */
    public function it_gets_the_timestamps_property_from_the_model()
    {
        $user = $this->user();

        $this->assertTrue($user->timestamps);

        $user->model()->timestamps = false;

        $this->assertFalse($user->timestamps);
    }

    /** @test */
    public function it_gets_super_correctly_on_the_model()
    {
        $user = $this->makeUser();

        $this->assertNull($user->super);

        $user->super = true;
        $user->save();

        $this->assertTrue($user->super);
        $this->assertTrue($user->model()->super);

        $user->super = false;
        $user->save();

        $this->assertFalse($user->super);
        $this->assertFalse($user->model()->super);
    }
}
