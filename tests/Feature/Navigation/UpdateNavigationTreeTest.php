<?php

namespace Tests\Feature\Navigation;

use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateNavigationTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesRoles;

    /** @test */
    public function it_updates_the_tree()
    {
        $this->withoutExceptionHandling();
        $user = tap(User::make()->makeSuper())->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [
            ['title' => 'URL', 'url' => 'http://example.com'],
            ['title' => 'Just Text'],
            ['entry' => '123'],
            ['entry' => '456'],
            ['title' => 'Being removed'],
        ])->save();

        $this
            ->actingAs($user)
            ->update($nav, ['pages' => [
                [
                    'values' => ['title' => 'Updated URL', 'url' => 'http://updated-example.com'],
                    'localizedFields' => ['title', 'url'],
                    'children' => [],
                ],
                [
                    'id' => '123',
                    'values' => [
                        'title' => 'Entry 123 Title',
                        'foo' => 'foo123',
                        'bar' => 'bar123',
                        'baz' => 'baz123',
                        'qux' => 'qux123',
                    ],
                    'localizedFields' => [],
                    'children' => [],
                ],
                [
                    'values' => ['title' => 'Updated Just Text'],
                    'localizedFields' => ['title'],
                    'children' => [
                        [
                            'id' => '456',
                            'values' => [
                                'title' => 'Overridden title',
                                'foo' => 'Overridden foo',
                                'bar' => 'bar456',
                                'baz' => [],
                                'qux' => null,
                            ],
                            'localizedFields' => ['title', 'foo', 'baz', 'qux'],
                            'children' => [],
                        ],
                    ],
                ],
            ]])
            ->assertOk();

        $this->assertEquals([
            ['title' => 'Updated URL', 'url' => 'http://updated-example.com'],
            ['entry' => '123'],
            ['title' => 'Updated Just Text', 'children' => [
                ['entry' => '456', 'title' => 'Overridden title', 'data' => [
                    'foo' => 'Overridden foo',
                    'baz' => [],   // Intentionally not stripped out so that empty values can be saved on the page when the
                    'qux' => null, // entry has a value. If they were stripped out, it would fall back to the entry's value.
                ]],
            ]],
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
            'pages' => [],
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
