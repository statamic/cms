<?php

namespace Tests\Permissions;

use Tests\TestCase;
use Statamic\API\File;
use Statamic\API\Role as RoleAPI;
use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\Role;
use Statamic\Contracts\Permissions\RoleRepository;

class FeatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app(RoleRepository::class)->path(__DIR__.'/__fixtures__/roles.yaml');
    }

    /** @test */
    function it_gets_the_roles()
    {
        $roles = RoleAPI::all();

        $this->assertInstanceOf(Collection::class, $roles);
        $this->assertCount(3, $roles);
        $this->assertEveryItemIsInstanceOf(Role::class, $roles);
    }

    /** @test */
    function it_gets_a_role()
    {
        tap(RoleAPI::find('one'), function ($role) {
            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('one', $role->handle());
            $this->assertEquals('One', $role->title());
            $this->assertEquals(['foo', 'bar'], $role->permissions()->all());
            $this->assertFalse($role->isSuper());
        });

        tap(RoleAPI::find('empty'), function ($role) {
            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('empty', $role->handle());
            $this->assertEquals('Empty', $role->title());
            $this->assertEquals([], $role->permissions()->all());
            $this->assertFalse($role->isSuper());
        });

        tap(RoleAPI::find('super'), function ($role) {
            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('super', $role->handle());
            $this->assertEquals('Super', $role->title());
            $this->assertEquals(['super', 'another'], $role->permissions()->all());
            $this->assertTrue($role->isSuper());
        });

        $this->assertNull(RoleAPI::find('unknown'));
    }

    /** @test */
    function it_checks_if_a_role_exists()
    {
        $this->assertTrue(RoleAPI::exists('one'));
        $this->assertTrue(RoleAPI::exists('empty'));
        $this->assertTrue(RoleAPI::exists('super'));
        $this->assertFalse(RoleAPI::exists('unknown'));
    }

    /** @test */
    function it_works_with_an_empty_file()
    {
        File::shouldReceive('exists')->andReturnTrue();
        File::shouldReceive('get')->andReturn('');

        tap(RoleAPI::all(), function ($roles) {
            $this->assertInstanceOf(Collection::class, $roles);
            $this->assertCount(0, $roles);
        });

        $this->assertNull(RoleAPI::find('test'));
        $this->assertFalse(RoleAPI::exists('test'));
    }

    /** @test */
    function it_works_with_no_file()
    {
        File::shouldReceive('exists')->andReturnFalse();

        tap(RoleAPI::all(), function ($roles) {
            $this->assertInstanceOf(Collection::class, $roles);
            $this->assertCount(0, $roles);
        });

        $this->assertNull(RoleAPI::find('test'));
        $this->assertFalse(RoleAPI::exists('test'));
    }
}
