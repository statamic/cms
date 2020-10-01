<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\File\Role;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Facades;
use Tests\Auth\PermissibleContractTests;
use Tests\Auth\UserContractTests;
use Tests\Preferences\HasPreferencesTests;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class EloquentUserTest extends TestCase
{
    use UserContractTests, PermissibleContractTests, HasPreferencesTests, WithFaker;

    private $num = 0;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config(['statamic.users.repository' => 'eloquent']);

        // TODO: The migration has been added into the test, but the implementation could be broken if the real
        // migration is different from what's in here. We should find a way to reference the actual migrations.
        $this->loadMigrationsFrom(__DIR__.'/__migrations__');
    }

    /** @test */
    public function it_gets_roles_already_in_the_db_without_explicitly_assigning_them()
    {
        $roleA = new class extends Role {
            public function handle(string $handle = null)
            {
                return 'a';
            }
        };
        $roleB = new class extends Role {
            public function handle(string $handle = null)
            {
                return 'b';
            }
        };
        $roleC = new class extends Role {
            public function handle(string $handle = null)
            {
                return 'c';
            }
        };
        $roleD = new class extends Role {
            public function handle(string $handle = null)
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

        \DB::table('role_user')->insert([
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
        $num = ++$this->num;

        $user = User::create([
            'name' => "Test Testerson {$num}",
            'email' => "test-{$num}@test.com",
            'remember_token' => str_random(10),
        ]);

        return (new EloquentUser)->model($user);
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
        ];
    }

    public function additionalDataValues()
    {
        $lt7 = version_compare(app()->version(), 7, '<');

        return [
            'created_at' => $lt7 ? now()->format('Y-m-d H:i:s') : now()->toISOString(),
            'updated_at' => $lt7 ? now()->format('Y-m-d H:i:s') : now()->toISOString(),
        ];
    }
}
