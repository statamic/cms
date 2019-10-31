<?php

namespace Tests\Permissions;

use Illuminate\Support\Collection;
use Statamic\Auth\Permission;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    /** @test */
    function it_makes_a_tree()
    {
        $permission = (new Permission)->value('one');

        $this->assertEquals([
            [
                'value' => 'one',
                'label' => 'one',
                'description' => null,
                'group' => null,
                'children' => []
            ]
        ], $permission->toTree());
    }

    /** @test */
    function it_makes_tree_with_children()
    {
        $permission = (new Permission)
            ->value('parent')
            ->group('foo');

        $permission->children([
            (new Permission)->value('child')->label('Child!')
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
                        'children' => []
                    ],
                ]
            ]
        ], $permission->toTree());
    }

    /** @test */
    function it_adds_a_label()
    {
        $permission = (new Permission)->value('test');
        $this->assertEquals('test', $permission->label());
        $this->assertEquals('test', $permission->toTree()[0]['label']);

        $return = $permission->label('The label');

        $this->assertEquals($permission, $return);
        $this->assertEquals('The label', $permission->label());
        $this->assertEquals('The label', $permission->toTree()[0]['label']);
    }

    /** @test */
    function it_adds_a_description()
    {
        $permission = (new Permission)->value('test');
        $this->assertNull($permission->description());
        $this->assertNull($permission->toTree()[0]['description']);

        $return = $permission->description('The description');

        $this->assertEquals($permission, $return);
        $this->assertEquals('The description', $permission->description());
        $this->assertEquals('The description', $permission->toTree()[0]['description']);
    }

    /** @test */
    function a_translation_can_be_used_for_the_label()
    {
        $permission = (new Permission)->value('test');
        $this->assertEquals('test', $permission->label());
        $this->assertEquals('test', $permission->toTree()[0]['label']);

        $return = $permission->translation('statamic::permissions.configure_collections');

        $this->assertEquals($permission, $return);
        $this->assertEquals('Configure Collections', $permission->label());
        $this->assertEquals('Configure Collections', $permission->toTree()[0]['label']);
    }

    /** @test */
    function it_adds_a_group()
    {
        $permission = (new Permission)->value('test');
        $this->assertNull($permission->group());
        $this->assertNull($permission->toTree()[0]['group']);

        $return = $permission->group('the-group');

        $this->assertEquals($permission, $return);
        $this->assertEquals('the-group', $permission->group());
        $this->assertEquals('the-group', $permission->toTree()[0]['group']);
    }

    /** @test */
    function it_gets_its_permissions_when_replacements_are_not_defined()
    {
        $permission = (new Permission)->value('test');

        $this->assertInstanceOf(Collection::class, $permission->permissions());
        $this->assertEquals([$permission], $permission->permissions()->all());
    }

    /** @test */
    function it_gets_its_permissions_when_replacements_are_defined()
    {
        $permission = (new Permission)
            ->value('view {handle} entries')
            ->label('Viewable :handle')
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
            ['view first entries', 'Viewable FIRST', 'test-group'],
            ['view second entries', 'Viewable SECOND', 'test-group'],
        ], $permission->permissions()->map(function ($permission) {
            return [
                $permission->value(),
                $permission->label(),
                $permission->group(),
            ];
        })->all());
        $this->assertCount(2, $permission->toTree());

        app('translator')->addNamespace('test', __DIR__.'/__fixtures__/lang');
        $translatedPermission = $permission->translation('test::test.edit_permission');
        $this->assertEquals([
            'Editable FIRST',
            'Editable SECOND',
        ], $translatedPermission->permissions()->map->label()->all());
    }

    /** @test */
    function it_combines_replacements_and_children()
    {
        app('translator')->addNamespace('test', __DIR__.'/__fixtures__/lang');

        $permission = (new Permission)
            ->value('view {handle} entries')
            ->label('Viewable :handle')
            ->group('test-group')
            ->children([
                (new Permission)->value('edit {handle} entries')->translation('test::test.edit_permission'),
                (new Permission)->value('publish {handle} entries')->label('Publishable :handle'),
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
            ['view first entries', 'Viewable FIRST', 'test-group'],
            ['view second entries', 'Viewable SECOND', 'test-group'],
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
                'label' => 'Viewable FIRST',
                'description' => null,
                'group' => 'test-group',
                'children' => [
                    [
                        'value' => 'edit first entries',
                        'label' => 'Editable FIRST',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => []
                    ],
                    [
                        'value' => 'publish first entries',
                        'label' => 'Publishable FIRST',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => []
                    ],
                ]
            ],
            [
                'value' => 'view second entries',
                'label' => 'Viewable SECOND',
                'description' => null,
                'group' => 'test-group',
                'children' => [
                    [
                        'value' => 'edit second entries',
                        'label' => 'Editable SECOND',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => []
                    ],
                    [
                        'value' => 'publish second entries',
                        'label' => 'Publishable SECOND',
                        'description' => null,
                        'group' => 'test-group',
                        'children' => []
                    ],
                ]
            ]
        ], $permission->toTree());
    }
}