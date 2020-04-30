<?php

namespace Tests\Permissions;

use Illuminate\Support\Collection;
use Statamic\Auth\Permissions;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    /** @test */
    public function it_registers_a_permission()
    {
        $permissions = new Permissions;
        $this->assertInstanceOf(Collection::class, $permissions->all());
        $this->assertCount(0, $permissions->all());

        $permission = $permissions->make('one');
        $this->assertCount(0, $permissions->all());

        $permissions->register($permission);
        $this->assertEquals(['one' => $permission], $permissions->all()->all());
        $this->assertEquals($permission, $permissions->get('one'));
    }

    /** @test */
    public function it_registers_a_permission_with_a_closure()
    {
        $permissions = new Permissions;
        $this->assertInstanceOf(Collection::class, $permissions->all());
        $this->assertCount(0, $permissions->all());

        $permission = $permissions->make('one');
        $this->assertCount(0, $permissions->all());

        $permissions->register($permission, function ($p) {
            $p->value('adjusted');
        });
        $this->assertCount(1, $permissions->all());
        $this->assertEquals([
            'adjusted' => $permission,
        ], $permissions->all()->all());
    }

    /** @test */
    public function it_registers_a_permission_via_a_string()
    {
        $permissions = new Permissions;

        $permissions->register('one');

        $this->assertCount(1, $permissions->all());
        $this->assertEquals('one', $permissions->all()->first()->value());
        $this->assertCount(0, $permissions->all()->first()->children());
    }

    /** @test */
    public function it_registers_a_permission_via_a_string_and_closure()
    {
        $permissions = new Permissions;

        $permissions->register('one', function ($permission) use ($permissions) {
            $permission->children([
                $permissions->make('two'),
            ]);
        });

        $this->assertCount(2, $permissions->all());
        $this->assertEquals(['one', 'two'], $permissions->all()->keys()->all());
        $this->assertCount(1, $permissions->all()['one']->children());
        $this->assertEquals('two', $permissions->all()->first()->children()->first()->value());
    }

    /** @test */
    public function any_permissions_registered_within_a_group_callback_will_belong_to_that_group()
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

        $four = null;
        $permissions->group('foo', function () use ($permissions, &$four) {
            $four = $permissions->register('four');
        });

        $all = $permissions->all();
        $this->assertEquals('foo', $one->group());
        $this->assertEquals('foo', $two->group());
        $this->assertEquals('bar', $three->group());
        $this->assertEquals('foo', $four->group());
    }

    /** @test */
    public function it_makes_a_tree()
    {
        $this->setupComplicatedTest($permissions = new Permissions);

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
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'handle' => 'group-with-replacements',
                'label' => 'Group with Replacements',
                'permissions' => [
                    [
                        'value' => 'four first',
                        'label' => 'four FIRST',
                        'description' => null,
                        'group' => 'group-with-replacements',
                        'children' => [
                            [
                                'value' => 'replaced child first',
                                'label' => 'Replaced FIRST',
                                'description' => null,
                                'group' => 'group-with-replacements',
                                'children' => [
                                    [
                                        'value' => 'replaced nested child first',
                                        'label' => 'Replaced Nested FIRST',
                                        'description' => null,
                                        'group' => 'group-with-replacements',
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'value' => 'four second',
                        'label' => 'four SECOND',
                        'description' => null,
                        'group' => 'group-with-replacements',
                        'children' => [
                            [
                                'value' => 'replaced child second',
                                'label' => 'Replaced SECOND',
                                'description' => null,
                                'group' => 'group-with-replacements',
                                'children' => [
                                    [
                                        'value' => 'replaced nested child second',
                                        'label' => 'Replaced Nested SECOND',
                                        'description' => null,
                                        'group' => 'group-with-replacements',
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
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
                            ],
                        ],
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

    /** @test */
    public function it_gets_all_permissions_in_a_flattened_structure()
    {
        $this->setupComplicatedTest($permissions = new Permissions);

        $all = $permissions->all();

        $this->assertEquals(collect([
            'one',
            'child-one',
            'child-two',

            'two',
            'child-three',
            'nested-child',

            'three',

            'four {placeholder}',
            'replaced child {placeholder}',
            'replaced nested child {placeholder}',
        ])->sort()->values()->all(), $all->keys()->sort()->values()->all());
    }

    /** @test */
    public function existing_permissions_can_be_modified()
    {
        $permissions = new Permissions;

        $permissions->register('one', function ($permission) {
            $permission->label('Test');
        });

        $this->assertEquals(['one' => 'Test'], $permissions->all()->map->label()->all());

        $permissions->get('one')->label('Modified');

        $this->assertEquals(['one' => 'Modified'], $permissions->all()->map->label()->all());
    }

    public function setupComplicatedTest($permissions)
    {
        $permissions->register('one', function ($permission) use ($permissions) {
            $permission->children([
                $permissions->make('child-one'),
                $permissions->make('child-two'),
            ]);
        });

        $permissions->group('test', 'Test Group', function () use ($permissions) {
            $permissions->register('two', function ($permission) use ($permissions) {
                $permission->children([
                    $permissions->make('child-three')->children([
                        $permissions->make('nested-child'),
                    ]),
                ]);
            });
        });

        $permissions->register('three');

        $permissions->group('group-with-replacements', 'Group with Replacements', function () use ($permissions) {
            $permissions->register('four {placeholder}', function ($permission) use ($permissions) {
                $permission->children([
                    $permissions->make('replaced child {placeholder}')->label('Replaced :placeholder')->children([
                        $permissions->make('replaced nested child {placeholder}')
                                    ->label('Replaced Nested :placeholder'),
                    ]),
                ])->replacements('placeholder', function () {
                    return [
                        ['value' => 'first', 'label' => 'FIRST'],
                        ['value' => 'second', 'label' => 'SECOND'],
                    ];
                });
            });
        });
    }
}
