<?php

namespace Tests\Permissions;

use Tests\TestCase;
use Faker\Generator as Faker;
use Statamic\Data\Users\Eloquent\User;
use Statamic\Data\Users\Eloquent\Model;
use Illuminate\Database\Eloquent\Factory;

class EloquentPermissibleTest extends TestCase
{
    use PermissibleContractTests;

    protected function setUp()
    {
        parent::setUp();

        // TODO: The migration has been added into the test, but the implementation could be broken if the real
        // migration is different from what's in here. We should find a way to reference the actual migrations.
        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        app(Factory::class)->define(Model::class, function (Faker $faker) {
            return [
                'id' => $faker->randomDigit,
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => str_random(10),
            ];
        });
    }

    protected function createPermissible()
    {
        return tap(new User)
            ->model(factory(Model::class)->make());
    }
}
