<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades;
use Statamic\Support\Str;
use Tests\Auth\PermissibleContractTests;
use Tests\Auth\UserContractTests;
use Tests\Preferences\HasPreferencesTests;
use Tests\TestCase;

#[Group('2fa')]
class EloquentUserTest extends TestCase
{
    use HasPreferencesTests, PermissibleContractTests, UserContractTests, WithFaker;

    public static $migrationsGenerated = false;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config(['statamic.users.repository' => 'eloquent']);

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
        // Prevent error about null password during the down migration.
        User::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    #[Test]
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

        $this->assertSame(Facades\User::query()->whereRole('a')->get()->first()->id(), $user->id());

        $userTwo = $this->createPermissible();
        $userThree = $this->createPermissible();
        $userFour = $this->createPermissible();

        \DB::table(config('statamic.users.tables.role_user', 'role_user'))->insert([
            ['user_id' => $userTwo->id(), 'role_id' => 'a'],
            ['user_id' => $userThree->id(), 'role_id' => 'b'],
            ['user_id' => $userFour->id(), 'role_id' => 'c'],
        ]);

        $this->assertCount(2, Facades\User::query()->whereRole('a')->get());
        $this->assertCount(2, Facades\User::query()->whereRole('b')->get());
        $this->assertCount(2, Facades\User::query()->whereRole('c')->get());
        $this->assertCount(1, Facades\User::query()->whereRole('d')->get());

        $this->assertSame([$user->email(), $userTwo->email()], Facades\User::query()->whereRole('a')->get()->map->email()->all());
        $this->assertSame([$user->email(), $userThree->email()], Facades\User::query()->whereRole('b')->get()->map->email()->all());
        $this->assertSame([$user->email()], Facades\User::query()->whereRole('b')->whereRole('c')->get()->map->email()->all());
        $this->assertSame([$user->email(), $userThree->email(), $userFour->email()], Facades\User::query()->whereRole('b')->orWhereRole('c')->get()->map->email()->all());

        $this->assertSame([$user->email(), $userTwo->email(), $userThree->email()], Facades\User::query()->whereRoleIn(['a', 'b'])->get()->map->email()->all());
        $this->assertSame([$user->email(), $userTwo->email(), $userThree->email(), $userFour->email()], Facades\User::query()->whereRoleIn(['a', 'b'])->orWhereRoleIn(['c'])->get()->map->email()->all());
    }

    #[Test]
    public function it_gets_groups_already_in_the_db_without_explicitly_assigning_them()
    {
        $roleA = new class extends UserGroup
        {
            public function handle(?string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends UserGroup
        {
            public function handle(?string $handle = null)
            {
                return 'b';
            }
        };
        $roleC = new class extends UserGroup
        {
            public function handle(?string $handle = null)
            {
                return 'c';
            }
        };
        $roleD = new class extends UserGroup
        {
            public function handle(?string $handle = null)
            {
                return 'd';
            }
        };

        Facades\UserGroup::shouldReceive('find')->with('a')->andReturn($roleA);
        Facades\UserGroup::shouldReceive('find')->with('b')->andReturn($roleB);
        Facades\UserGroup::shouldReceive('find')->with('c')->andReturn($roleC);
        Facades\UserGroup::shouldReceive('find')->with('d')->andReturn($roleD);
        Facades\UserGroup::shouldReceive('find')->with('unknown')->andReturnNull();

        $user = $this->createPermissible();

        \DB::table(config('statamic.users.tables.group_user', 'group_user'))->insert([
            ['user_id' => $user->id(), 'group_id' => 'a'],
            ['user_id' => $user->id(), 'group_id' => 'b'],
            ['user_id' => $user->id(), 'group_id' => 'c'],
            ['user_id' => $user->id(), 'group_id' => 'd'],
        ]);

        $this->assertInstanceOf(Collection::class, $user->groups());
        $this->assertCount(4, $user->groups());
        $this->assertEveryItemIsInstanceOf(UserGroupContract::class, $user->groups());
        $this->assertEquals([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
        ], $user->groups()->map->handle()->all());

        $this->assertSame(Facades\User::query()->whereGroup('a')->get()->first()->id(), $user->id());

        $userTwo = $this->createPermissible();
        $userThree = $this->createPermissible();
        $userFour = $this->createPermissible();

        \DB::table(config('statamic.users.tables.group_user', 'group_user'))->insert([
            ['user_id' => $userTwo->id(), 'group_id' => 'a'],
            ['user_id' => $userThree->id(), 'group_id' => 'b'],
            ['user_id' => $userFour->id(), 'group_id' => 'c'],
        ]);

        $this->assertCount(2, Facades\User::query()->whereGroup('a')->get());
        $this->assertCount(2, Facades\User::query()->whereGroup('b')->get());
        $this->assertCount(2, Facades\User::query()->whereGroup('c')->get());
        $this->assertCount(1, Facades\User::query()->whereGroup('d')->get());

        $this->assertSame([$user->email(), $userTwo->email()], Facades\User::query()->whereGroup('a')->get()->map->email()->all());
        $this->assertSame([$user->email(), $userThree->email()], Facades\User::query()->whereGroup('b')->get()->map->email()->all());
        $this->assertSame([$user->email()], Facades\User::query()->whereGroup('b')->whereGroup('c')->get()->map->email()->all());
        $this->assertSame([$user->email(), $userThree->email(), $userFour->email()], Facades\User::query()->whereGroup('b')->orWhereGroup('c')->get()->map->email()->all());

        $this->assertSame([$user->email(), $userTwo->email(), $userThree->email()], Facades\User::query()->whereGroupIn(['a', 'b'])->get()->map->email()->all());
        $this->assertSame([$user->email(), $userTwo->email(), $userThree->email(), $userFour->email()], Facades\User::query()->whereGroupIn(['a', 'b'])->orWhereGroupIn(['c'])->get()->map->email()->all());
    }

    public function makeUser()
    {
        return (new EloquentUser)
            ->model(User::create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                // 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => Str::random(10),
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

    #[Test]
    public function it_gets_the_timestamps_property_from_the_model()
    {
        $user = $this->user();

        $this->assertTrue($user->timestamps);

        $user->model()->timestamps = false;

        $this->assertFalse($user->timestamps);
    }

    #[Test]
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

    #[Test]
    public function it_does_not_save_null_values_on_the_model()
    {
        $user = $this->user();

        $user->set('null_field', null);
        $user->set('not_null_field', true);

        $attributes = $user->model()->getAttributes();

        $this->assertArrayNotHasKey('null_field', $attributes);
        $this->assertTrue($attributes['not_null_field']);

        $user->merge([
            'null_field' => null,
            'not_null_field' => false,
        ]);

        $attributes = $user->model()->getAttributes();

        $this->assertArrayNotHasKey('null_field', $attributes);
        $this->assertFalse($attributes['not_null_field']);
    }
}
