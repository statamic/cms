<?php

namespace Tests\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    #[Test]
    public function it_gets_and_sets_the_title()
    {
        $role = new Role;
        $role->handle('testing');
        $this->assertEquals('Testing', $role->title());

        $return = $role->title('Test');

        $this->assertEquals('Test', $role->title());
        $this->assertEquals($role, $return);
    }

    #[Test]
    public function it_gets_and_sets_the_handle()
    {
        $role = new Role;
        $this->assertNull($role->handle());

        $return = $role->handle('test');

        $this->assertEquals('test', $role->handle());
        $this->assertEquals($role, $return);
    }

    #[Test]
    public function it_gets_and_adds_permissions()
    {
        $role = new Role;
        $this->assertInstanceOf(Collection::class, $role->permissions());
        $this->assertCount(0, $role->permissions());

        $return = $role->addPermission('one');
        $role->addPermission(['two', 'three']);

        $this->assertInstanceOf(Collection::class, $role->permissions());
        $this->assertEquals(['one', 'two', 'three'], $role->permissions()->all());
        $this->assertEquals($role, $return);
    }

    #[Test]
    public function it_sets_all_permissions()
    {
        $role = new Role;
        $role->addPermission('one');

        $return = $role->permissions(['two', 'three']);

        $this->assertInstanceOf(Collection::class, $role->permissions());
        $this->assertEquals(['two', 'three'], $role->permissions()->all());
        $this->assertEquals($role, $return);
    }

    #[Test]
    public function permissions_get_deduplicated()
    {
        $role = new Role;
        $role->addPermission(['foo', 'bar']);
        $role->addPermission(['foo', 'baz']);

        $this->assertEquals(['foo', 'bar', 'baz'], $role->permissions()->all());
    }

    #[Test]
    public function it_removes_permissions()
    {
        $role = (new Role)->addPermission(['foo', 'bar', 'baz', 'qux']);

        $return = $role->removePermission('foo');
        $role->removePermission(['baz', 'qux']);

        $this->assertEquals(['bar'], $role->permissions()->all());
        $this->assertEquals($role, $return);
    }

    #[Test]
    public function it_checks_if_it_has_permission()
    {
        $role = (new Role)->addPermission('foo');

        $this->assertTrue($role->hasPermission('foo'));
        $this->assertFalse($role->hasPermission('bar'));
    }

    #[Test]
    public function it_checks_if_it_has_super_permissions()
    {
        $superRole = (new Role)->addPermission('super');
        $nonSuperRole = (new Role)->addPermission('something else');

        $this->assertTrue($superRole->isSuper());
        $this->assertFalse($nonSuperRole->isSuper());
    }

    #[Test]
    public function it_can_be_saved()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_can_be_deleted()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $role = (new Role)->handle('test')->title('Test');

        $role
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $role->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $role[$key]));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $role = (new Role)->handle('test')->title('Test');

        $this->assertInstanceOf(Arrayable::class, $role);

        collect($role->toArray())
            ->each(fn ($value, $key) => $this->assertEquals($value, $role->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value, $role[$key]));
    }
}
