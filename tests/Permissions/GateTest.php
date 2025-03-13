<?php

namespace Tests\Permissions;

use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Permission;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GateTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        Role::make('test')->delete();

        User::all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('gateProvider')]
    public function gate_checks($userCallback, $permission, $expectsToBeAllowed)
    {
        // Add a Statamic permission. By adding a custom one it proves
        // that it's not just "core" permissions that will work, but
        // also any permission registered into Statamic.
        Permission::extend(function () {
            Permission::register('statamic');
        });

        Collection::make('blog')->save();

        // Add a role that has the permission since permissions
        // cannot be applied directly to users.
        Role::make('test')
            ->addPermission('statamic')
            ->addPermission('edit blog entries')
            ->save();

        // Add a gate, which is how someone would define
        // something completely separate from Statamic.
        Gate::define('gate', fn ($user) => $user->email === 'allowed@domain.com' ? true : null);

        $this->actingAs($userCallback()->save());

        $this->assertEquals(
            $expectsToBeAllowed,
            Gate::allows($permission),
            'User should '.($expectsToBeAllowed ? '' : 'not ').'be allowed.'
        );
    }

    public static function gateProvider()
    {
        return [
            'statamic permission, super user' => [
                fn () => User::make()->makeSuper(),
                'statamic',
                true,
            ],
            'statamic permission, user with permission' => [
                fn () => User::make()->assignRole('test'),
                'statamic',
                true,
            ],
            'statamic permission, user without permission' => [
                fn () => User::make(),
                'statamic',
                false,
            ],
            'statamic policy permission, super user' => [
                fn () => User::make()->makeSuper(),
                'edit blog entries',
                true,
            ],
            'statamic policy permission, user with permission' => [
                fn () => User::make()->assignRole('test'),
                'edit blog entries',
                true,
            ],
            'statamic policy permission, user without permission' => [
                fn () => User::make(),
                'edit blog entries',
                false,
            ],
            'non-statamic permission, super user' => [
                fn () => User::make()->makeSuper(),
                'gate',
                false,
            ],
            'non-statamic permission, user with permission' => [
                fn () => User::make()->email('allowed@domain.com'),
                'gate',
                true,
            ],
            'non-statamic permission, user without permission' => [
                fn () => User::make()->email('denied@domain.com'),
                'gate',
                false,
            ],
        ];
    }
}
