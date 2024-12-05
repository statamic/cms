<?php

namespace Tests\Permissions;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Permission;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    #[Test]
    public function it_makes_a_tree()
    {
        $permission = (new Permission)->value('one');

        $this->assertEquals([
            [
                'value' => 'one',
                'label' => 'one',
                'description' => null,
                'group' => null,
                'children' => [],
            ],
        ], $permission->toTree());
    }

    #[Test]
    public function it_makes_tree_with_children()
    {
        $permission = (new Permission)
            ->value('parent')
            ->group('foo');

        $permission->children([
            (new Permission)->value('child')->label('Child!'),
        ]);

        $this->assertEquals([
            [
                'value' => 'parent',
                'label' => 'parent',
                'description' => null,
                'group' => 'foo',
                'children' => [
                    [
                        'value' => 'child',
                        'label' => 'Child!',
                        'description' => null,
                        'group' => 'foo',
                        'children' => [],
                    ],
                ],
            ],
        ], $permission->toTree());
    }

    #[Test]
    public function it_adds_a_child()
    {
        $permission = (new Permission)->value('test')->group('testgroup');
        $this->assertEmpty($permission->children());

        $permission->addChild(
            $child = (new Permission)->value('child')
        );

        $children = $permission->children();
        $this->assertCount(1, $children);
        $this->assertEquals($child, $children[0]);
        $this->assertEquals('testgroup', $children[0]->group());
    }

    #[Test]
    public function it_adds_a_label()
    {
        $permission = (new Permission)->value('test');
        $this->assertEquals('test', $permission->label());
        $this->assertEquals('test', $permission->toTree()[0]['label']);

        $return = $permission->label('The label');

        $this->assertEquals($permission, $return);
        $this->assertEquals('The label', $permission->label());
        $this->assertEquals('The label', $permission->toTree()[0]['label']);
    }

    #[Test]
    public function it_adds_a_description()
    {
        $permission = (new Permission)->value('test');
        $this->assertNull($permission->description());
        $this->assertNull($permission->toTree()[0]['description']);

        $return = $permission->description('The description');

        $this->assertEquals($permission, $return);
        $this->assertEquals('The description', $permission->description());
        $this->assertEquals('The description', $permission->toTree()[0]['description']);
    }

    #[Test]
    public function it_adds_a_group()
    {
        $permission = (new Permission)->value('test');
        $this->assertNull($permission->group());
        $this->assertNull($permission->toTree()[0]['group']);

        $return = $permission->group('the-group');

        $this->assertEquals($permission, $return);
        $this->assertEquals('the-group', $permission->group());
        $this->assertEquals('the-group', $permission->toTree()[0]['group']);
    }

    #[Test]
    public function it_gets_its_permissions_when_replacements_are_not_defined()
    {
        $permission = (new Permission)->value('test');

        $this->assertInstanceOf(Collection::class, $permission->permissions());
        $this->assertEquals([$permission], $permission->permissions()->all());
    }

    #[Test]
    public function it_gets_its_permissions_when_replacements_are_defined()
    {
        $permission = (new Permission)
            ->value('view {handle} entries')
            ->group('test-group')
            ->replacements('handle', function () {
                return [
                    ['value' => 'first', 'label' => 'FIRST'],
                    ['value' => 'second', 'label' => 'SECOND'],
                ];
            });

        $this->assertInstanceOf(Collection::class, $permission->permissions());
        $this->assertNotContains($permission, $permission->permissions()->all());
        $this->assertEquals([
            ['view first entries', 'view FIRST entries', 'test-group'],
            ['view second entries', 'view SECOND entries', 'test-group'],
        ], $permission->permissions()->map(function ($permission) {
            return [
                $permission->value(),
                $permission->label(),
                $permission->group(),
            ];
        })->all());
        $this->assertCount(2, $permission->toTree());

        $permission->label('Viewable :handle');
        $this->assertEquals(['Viewable FIRST', 'Viewable SECOND'], $permission->permissions()->map->label()->all());
    }

    #[Test]
    public function it_combines_replacements_and_children()
    {
        $permission = (new Permission)
            ->value('view {handle} entries')
            ->group('test-group')
            ->children([
                (new Permission)->value('edit {handle} entries')->children([
                    (new Permission)->value('delete {handle} entries'),
                ]),
                (new Permission)->value('publish {handle} entries'),
            ])
            ->replacements('handle', function () {
                return [
                    ['value' => 'first', 'label' => 'FIRST'],
                    ['value' => 'second', 'label' => 'SECOND'],
                ];
            });

        $permissions = $permission->permissions();
        $this->assertInstanceOf(Collection::class, $permissions);
        $this->assertNotContains($permission, $permissions->all());
        $this->assertEquals([
            ['view first entries', 'view FIRST entries', 'test-group'],
            ['view second entries', 'view SECOND entries', 'test-group'],
        ], $permissions->map(function ($permission) {
            return [
                $permission->value(),
                $permission->label(),
                $permission->group(),
            ];
        })->all());
        $this->assertCount(2, $permission->toTree());
        $this->assertCount(2, $permission->toTree()[0]['children']);
        $this->assertCount(2, $permission->toTree()[1]['children']);

        $this->assertEquals([
            [
                'value' => 'view first entries',
                'label' => 'view FIRST entries',
                'description' => null,
                'group' => 'test-group',
                'children' => [
                    [
                        'value' => 'edit first entries',
                        'label' => 'edit FIRST entries',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => [
                            [
                                'value' => 'delete first entries',
                                'label' => 'delete FIRST entries',
                                'description' => null,
                                'group' => 'test-group',
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'value' => 'publish first entries',
                        'label' => 'publish FIRST entries',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => [],
                    ],
                ],
            ],
            [
                'value' => 'view second entries',
                'label' => 'view SECOND entries',
                'description' => null,
                'group' => 'test-group',
                'children' => [
                    [
                        'value' => 'edit second entries',
                        'label' => 'edit SECOND entries',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => [
                            [
                                'value' => 'delete second entries',
                                'label' => 'delete SECOND entries',
                                'description' => null,
                                'group' => 'test-group',
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'value' => 'publish second entries',
                        'label' => 'publish SECOND entries',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => [],
                    ],
                ],
            ],
        ], $permission->toTree());

        $permission->label('Viewable :handle');
        $permission->children()[0]->label('Editable :handle');
        $permission->children()[1]->label('Publishable :handle');
        $tree = $permission->toTree();
        $this->assertEquals('Viewable FIRST', $tree[0]['label']);
        $this->assertEquals('Viewable SECOND', $tree[1]['label']);
        $this->assertEquals('Editable FIRST', $tree[0]['children'][0]['label']);
        $this->assertEquals('Publishable FIRST', $tree[0]['children'][1]['label']);
        $this->assertEquals('Editable SECOND', $tree[1]['children'][0]['label']);
        $this->assertEquals('Publishable SECOND', $tree[1]['children'][1]['label']);
    }
}
