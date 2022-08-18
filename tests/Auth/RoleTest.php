<?php

namespace Tests\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Statamic\Facades;
use Tests\TestCase;

class RoleTest extends TestCase
{
    /** @test */
    public function it_gets_and_sets_the_title()
    {
        $role = Facades\Role::make();
        $this->assertNull($role->title());

        $return = $role->title('Test');

        $this->assertEquals('Test', $role->title());
        $this->assertEquals($role, $return);
    }

    /** @test */
    public function it_gets_and_sets_the_handle()
    {
        $role = Facades\Role::make();
        $this->assertNull($role->handle());

        $return = $role->handle('test');

        $this->assertEquals('test', $role->handle());
        $this->assertEquals($role, $return);
    }

    /** @test */
    public function it_gets_and_adds_permissions()
    {
        $role = Facades\Role::make();
        $this->assertInstanceOf(Collection::class, $role->permissions());
        $this->assertCount(0, $role->permissions());

        $return = $role->addPermission('one');
        $role->addPermission(['two', 'three']);

        $this->assertInstanceOf(Collection::class, $role->permissions());
        $this->assertEquals(['one', 'two', 'three'], $role->permissions()->all());
        $this->assertEquals($role, $return);
    }

    /** @test */
    public function it_sets_all_permissions()
    {
        $role = Facades\Role::make();
        $role->addPermission('one');

        $return = $role->permissions(['two', 'three']);

        $this->assertInstanceOf(Collection::class, $role->permissions());
        $this->assertEquals(['two', 'three'], $role->permissions()->all());
        $this->assertEquals($role, $return);
    }

    /** @test */
    public function permissions_get_deduplicated()
    {
        $role = Facades\Role::make();
        $role->addPermission(['foo', 'bar']);
        $role->addPermission(['foo', 'baz']);

        $this->assertEquals(['foo', 'bar', 'baz'], $role->permissions()->all());
    }

    /** @test */
    public function it_removes_permissions()
    {
        $role = Facades\Role::make()->addPermission(['foo', 'bar', 'baz', 'qux']);

        $return = $role->removePermission('foo');
        $role->removePermission(['baz', 'qux']);

        $this->assertEquals(['bar'], $role->permissions()->all());
        $this->assertEquals($role, $return);
    }

    /** @test */
    public function it_checks_if_it_has_permission()
    {
        $role = Facades\Role::make()->addPermission('foo');

        $this->assertTrue($role->hasPermission('foo'));
        $this->assertFalse($role->hasPermission('bar'));
    }

    /** @test */
    public function it_checks_if_it_has_super_permissions()
    {
        $superRole = Facades\Role::make()->addPermission('super');
        $nonSuperRole = Facades\Role::make()->addPermission('something else');

        $this->assertTrue($superRole->isSuper());
        $this->assertFalse($nonSuperRole->isSuper());
    }

    /** @test */
    public function it_can_be_saved()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $role = Facades\Role::make()->handle('test')->title('Test');

        $role
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $role->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $role[$key]));
    }

    /** @test */
    public function it_is_arrayable()
    {
        $role = Facades\Role::make()->handle('test')->title('Test');

        $this->assertInstanceOf(Arrayable::class, $role);

        collect($role->toArray())
            ->each(fn ($value, $key) => $this->assertEquals($value, $role->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value, $role[$key]));
    }

    /** @test */
    public function it_gets_data()
    {
        $role = Facades\Role::make()->handle('test')->data([
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
        ], $role->data()->all());
    }

    /** @test */
    public function it_gets_blueprint_values()
    {
        $blueprint = Facades\Role::blueprint();
        $contents = $blueprint->contents();
        $contents['sections']['main']['fields'] = array_merge($contents['sections']['main']['fields'], [
            ['handle' => 'two', 'field' => ['type' => 'text']],
            ['handle' => 'four', 'field' => ['type' => 'text']],
            ['handle' => 'unused_in_bp', 'field' => ['type' => 'text']],
        ]);
        $blueprint->setContents($contents);
        Facades\Blueprint::shouldReceive('find')->with('user_group')->andReturn($blueprint);

        $data = [
            'one' => 'the "one" value on the role',
            'two' => 'the "two" value on the role and in the blueprint',
        ];

        $role = Facades\Role::make()
            ->handle('group_1')
            ->data($data);

        $this->assertEquals($role->get('one'), $data['one']);
        $this->assertEquals($role->get('two'), $data['two']);
    }
}
