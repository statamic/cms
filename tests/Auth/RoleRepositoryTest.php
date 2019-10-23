<?php

namespace Tests\Auth;

use Tests\TestCase;
use Statamic\Facades\File;
use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\Role;
use Statamic\Auth\File\RoleRepository;

class RoleRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = (new RoleRepository)->path(__DIR__.'/__fixtures__/roles.yaml');
    }

    /** @test */
    function it_gets_the_roles()
    {
        $roles = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $roles);
        $this->assertCount(3, $roles);
        $this->assertEveryItemIsInstanceOf(Role::class, $roles);
    }

    /** @test */
    function it_gets_a_role()
    {
        tap($this->repo->find('one'), function ($role) {
            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('one', $role->handle());
            $this->assertEquals('One', $role->title());
            $this->assertEquals(['foo', 'bar'], $role->permissions()->all());
            $this->assertFalse($role->isSuper());
        });

        tap($this->repo->find('empty'), function ($role) {
            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('empty', $role->handle());
            $this->assertEquals('Empty', $role->title());
            $this->assertEquals([], $role->permissions()->all());
            $this->assertFalse($role->isSuper());
        });

        tap($this->repo->find('super'), function ($role) {
            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('super', $role->handle());
            $this->assertEquals('Super', $role->title());
            $this->assertEquals(['super', 'another'], $role->permissions()->all());
            $this->assertTrue($role->isSuper());
        });

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    function it_checks_if_a_role_exists()
    {
        $this->assertTrue($this->repo->exists('one'));
        $this->assertTrue($this->repo->exists('empty'));
        $this->assertTrue($this->repo->exists('super'));
        $this->assertFalse($this->repo->exists('unknown'));
    }

    /** @test */
    function it_works_with_an_empty_file()
    {
        File::shouldReceive('exists')->andReturnTrue();
        File::shouldReceive('get')->andReturn('');

        tap($this->repo->all(), function ($roles) {
            $this->assertInstanceOf(Collection::class, $roles);
            $this->assertCount(0, $roles);
        });

        $this->assertNull($this->repo->find('test'));
        $this->assertFalse($this->repo->exists('test'));
    }

    /** @test */
    function it_works_with_no_file()
    {
        File::shouldReceive('exists')->andReturnFalse();

        tap($this->repo->all(), function ($roles) {
            $this->assertInstanceOf(Collection::class, $roles);
            $this->assertCount(0, $roles);
        });

        $this->assertNull($this->repo->find('test'));
        $this->assertFalse($this->repo->exists('test'));
    }
}
