<?php

namespace Tests\Permissions;

use Illuminate\Support\Collection;
use Statamic\Auth\Permissions;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    /** @test */
    function it_registers_a_permission()
    {
        $permissions = new Permissions;
        $this->assertInstanceOf(Collection::class, $permissions->all());
        $this->assertCount(0, $permissions->all());

        $permission = $permissions->make('one');
        $this->assertCount(0, $permissions->all());

        $permissions->register($permission);
        $this->assertCount(1, $permissions->all());
        $this->assertEquals([
            'one' => $permission,
        ], $permissions->all()->all());
    }

    /** @test */
    function it_registers_a_permission_via_a_string()
    {
        $permissions = new Permissions;

        $permissions->register('one');

        $this->assertCount(1, $permissions->all());
        $this->assertEquals('one', $permissions->all()->first()->value());
        $this->assertCount(0, $permissions->all()->first()->children());
    }

    /** @test */
    function it_registers_a_permission_via_a_string_and_closure()
    {
        $permissions = new Permissions;

        $permissions->register('one', function ($permission) use ($permissions) {
            $permission->children([
                $permissions->make('two')
            ]);
        });

        $this->assertCount(2, $permissions->all());
        $this->assertEquals(['one', 'two'], $permissions->all()->keys()->all());
        $this->assertCount(1, $permissions->all()['one']->children());
        $this->assertEquals('two', $permissions->all()->first()->children()->first()->value());
    }

    /** @test */
    function any_permissions_registered_within_a_group_callback_will_belong_to_that_group()
    {
        $permissions = new Permissions;

        $one = $two = null;
        $permissions->group('foo', 'Foo', function () use ($permissions, &$one, &$two) {
            $one = $permissions->register('one');
            $two = $permissions->register('two');
        });

        $three = null;
        $permissions->group('bar', 'Bar', function () use ($permissions, &$three) {
            $three = $permissions->register('three');
        });

        $all = $permissions->all();
        $this->assertEquals('foo', $one->group());
        $this->assertEquals('foo', $two->group());
        $this->assertEquals('bar', $three->group());
    }

    /** @test */
    function it_makes_a_tree()
    {
        $permissions = new Permissions;

        $permissionOne = $permissions->make('one')->children([
            $childPermissionOne = $permissions->make('child-one'),
            $childPermissionTwo = $permissions->make('child-two'),
        ]);

        $permissionTwo = null;
        $permissions->group('test', 'Test Group', function () use ($permissions, &$permissionTwo) {
            $permissionTwo = $permissions->make('two')->children([
                $childPermissionThree = $permissions->make('child-three')->children([
                    $nestedChildPermission = $permissions->make('nested-child'),
                ])
            ]);
        });

        $permissionThree = $permissions->make('three');

        $permissions->register($permissionOne);
        $permissions->register($permissionTwo);
        $permissions->register($permissionThree);

        $this->assertEquals([
            [
                'handle' => 'test',
                'label' => 'Test Group',
                'permissions' => [
                    [
                        'value' => 'two',
                        'label' => 'two',
                        'description' => null,
                        'group' => 'test',
                        'children' => [
                            [
                                'value' => 'child-three',
                                'label' => 'child-three',
                                'description' => null,
                                'group' => 'test',
                                'children' => [
                                    [
                                        'value' => 'nested-child',
                                        'label' => 'nested-child',
                                        'description' => null,
                                        'group' => 'test',
                                        'children' => []
                                    ]
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'handle' => 'misc',
                'label' => 'Miscellaneous',
                'permissions' => [
                    [
                        'value' => 'one',
                        'label' => 'one',
                        'description' => null,
                        'group' => null,
                        'children' => [
                            [
                                'value' => 'child-one',
                                'label' => 'child-one',
                                'description' => null,
                                'group' => null,
                                'children' => [],
                            ],
                            [
                                'value' => 'child-two',
                                'label' => 'child-two',
                                'description' => null,
                                'group' => null,
                                'children' => [],
                            ]
                        ]
                    ],
                    [
                        'value' => 'three',
                        'label' => 'three',
                        'description' => null,
                        'group' => null,
                        'children' => [],
                    ],
                ],
            ],
        ], $permissions->tree()->all());
    }
}