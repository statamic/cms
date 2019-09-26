<?php

namespace Tests\Auth\Eloquent;

use Tests\TestCase;
use Faker\Generator as Faker;
use Tests\Auth\UserContractTests;
use Illuminate\Database\Eloquent\Factory;
use Tests\Permissions\PermissibleContractTests;
use Statamic\Auth\Eloquent\User as EloquentUser;

class EloquentUserTest extends TestCase
{
    use UserContractTests, PermissibleContractTests;

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.users.repository' => 'eloquent']);

        // TODO: The migration has been added into the test, but the implementation could be broken if the real
        // migration is different from what's in here. We should find a way to reference the actual migrations.
        $this->loadMigrationsFrom(__DIR__ . '/__migrations__');

        app(Factory::class)->define(User::class, function (Faker $faker) {
            return [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                // 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => str_random(10),
            ];
        });
    }

    function makeUser() {
        return (new EloquentUser)
            ->model(factory(User::class)->create());
    }

    function createPermissible()
    {
        return $this->makeUser();
    }

    function additionalToArrayValues()
    {
        return [
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    function additionalDataValues()
    {
        return [
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
