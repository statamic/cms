<?php

namespace Tests\Feature\Navigation;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Structures\BranchIdGenerator;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateNavigationTreeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function mockTextFieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('text')
            ->andReturn(new class extends Fieldtype
            {
                public function process($value)
                {
                    if (! $value) {
                        return $value;
                    }

                    return $value.' (processed)';
                }
            });
    }

    /** @test */
    public function it_updates_the_tree()
    {
        $this->withoutExceptionHandling();
        $this->mockTextFieldtype();

        $blueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'text'],
            'baz' => ['type' => 'text'],
            'qux' => ['type' => 'text'],
        ]);
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('navigation.test')->andReturn($blueprint);
        BranchIdGenerator::shouldReceive('generate')->times(3)->andReturn('newly-generated-id1', 'newly-generated-id2', 'newly-generated-id3');

        $user = tap(User::make()->makeSuper())->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [
            ['id' => 'id1', 'title' => 'URL', 'url' => 'http://example.com', 'children' => [
                ['id' => 'id6', 'title' => 'Nested text'],
            ]],
            ['id' => 'id2', 'title' => 'Just Text'],
            ['id' => 'id3', 'entry' => '123'],
            ['id' => 'id4', 'entry' => '456'],
            ['id' => 'id5', 'title' => 'Being removed'],
        ])->save();

        $this
            ->actingAs($user)
            ->update($nav, [
                'pages' => [
                    ['id' => 'id1', 'children' => []],
                    ['id' => 'id3', 'children' => []],
                    ['id' => 'new-standard-page', 'children' => []],
                    ['id' => 'id2', 'children' => [
                        ['id' => 'id4', 'children' => []],
                    ]],
                    ['id' => 'new-entry-page', 'children' => []],
                    ['id' => 'new-entry-page-with-values', 'children' => []],
                ],
                'data' => [
                    'id1' => [
                        'values' => [
                            'title' => 'Updated URL',
                            'url' => 'http://updated-example.com',
                        ],
                        'localizedFields' => ['title', 'url'],
                    ],
                    'id2' => [
                        'values' => [
                            'title' => 'Updated Just Text',
                        ],
                        'localizedFields' => ['title'],
                    ],
                    'id4' => [
                        'entry' => '456',
                        'values' => [
                            'title' => 'Overridden title',
                            'foo' => 'Overridden foo',
                            'bar' => 'bar456',
                            'baz' => [],
                            'qux' => null,
                        ],
                        'localizedFields' => ['title', 'foo', 'baz', 'qux'],
                    ],
                    'new-standard-page' => [
                        'values' => [
                            'title' => 'New Branch',
                        ],
                        'localizedFields' => ['title'],
                        'new' => true,
                    ],
                    'new-entry-page' => [
                        'entry' => '789',
                        'new' => true,
                    ],
                    'new-entry-page-with-values' => [
                        'entry' => '910',
                        'new' => true,
                        'values' => [],
                        'localizedFields' => [],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJson([
                'generatedIds' => [
                    'new-standard-page' => 'newly-generated-id1',
                    'new-entry-page' => 'newly-generated-id2',
                ],
            ]);

        $this->assertEquals([
            ['id' => 'id1', 'title' => 'Updated URL (processed)', 'url' => 'http://updated-example.com (processed)'],
            ['id' => 'id3', 'entry' => '123'],
            ['id' => 'newly-generated-id1', 'title' => 'New Branch (processed)'],
            ['id' => 'id2', 'title' => 'Updated Just Text (processed)', 'children' => [
                ['id' => 'id4', 'entry' => '456', 'title' => 'Overridden title (processed)', 'data' => [
                    'foo' => 'Overridden foo (processed)',
                    'baz' => [],   // Intentionally not stripped out so that empty values can be saved on the page when the
                    'qux' => null, // entry has a value. If they were stripped out, it would fall back to the entry's value.
                ]],
            ]],
            ['id' => 'newly-generated-id2', 'entry' => '789'],
            ['id' => 'newly-generated-id3', 'entry' => '910'],
        ], $nav->in('en')->tree());
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission_to_reorder()
    {
        $this->markTestIncomplete();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->structureContents(['tree' => []]))->save();

        $this
            ->actingAs($user)
            ->update($collection, ['site' => 'en', 'pages' => []])
            ->assertForbidden();
    }

    public function update($nav, $payload = [])
    {
        $validParams = [
            'site' => 'en',
        ];

        return $this->patchJson(
            cp_route('navigation.tree.update', $nav->handle()),
            array_merge($validParams, $payload)
        );
    }

    /** @test */
    public function it_updates_a_specific_sites_tree()
    {
        $this->markTestIncomplete();
    }
}
