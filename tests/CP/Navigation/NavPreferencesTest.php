<?php

namespace Tests\CP\Navigation;

use Statamic\CP\Navigation\Nav;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NavPreferencesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    /** @test */
    public function it_can_reorder_sections()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $reorderedSections = ['Top Level', 'Users', 'Fields', 'Content', 'Tools'];

        // Recommended syntax...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
            ],
        ])->keys()->all());

        // Without nesting sections...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'top_level' => '@inherit',
            'users' => '@inherit',
            'fields' => '@inherit',
            'content' => '@inherit',
            'tools' => '@inherit',
        ])->keys()->all());

        // Merge unmentioned sections underneath...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
            ],
        ])->keys()->all());

        // Merge top level section at top...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'users' => '@inherit',
                'fields' => '@inherit',
            ],
        ])->keys()->all());

        // Always merge top level section at top, even when explicitly defining in middle...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'users' => '@inherit',
                'top_level' => '@inherit',
                'fields' => '@inherit',
            ],
        ])->keys()->all());

        // Ensure re-ordering sections still works when modifying a section...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'users' => '@inherit',
                'fields' => [
                    'items' => [
                        'top_level::dashboard' => '@alias',
                    ],
                ],
                'content' => '@inherit',
            ],
        ])->keys()->all());

        // If `reorder: false`, it should just use default section order...
        $this->assertEquals($defaultSections, $this->buildNavWithPreferences([
            'reorder' => false,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
            ],
        ])->keys()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals($defaultSections, $this->buildNavWithPreferences([
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
            ],
        ])->keys()->all());
    }

    /** @test */
    public function it_can_reorder_items_within_sections()
    {
        $defaultContentItems = ['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'];

        $this->assertEquals($defaultContentItems, $this->buildDefaultNav()->get('Content')->map->display()->all());

        $reorderedContentItems = ['Globals', 'Taxonomies', 'Collections', 'Navigation', 'Assets'];

        // Recommended syntax...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => true,
                'items' => [
                    'content::globals' => '@inherit',
                    'content::taxonomies' => '@inherit',
                    'content::collections' => '@inherit',
                    'content::navigation' => '@inherit',
                    'content::assets' => '@inherit',
                ],
            ],
        ])->get('Content')->map->display()->all());

        // Without nesting items...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => true,
                'content::globals' => '@inherit',
                'content::taxonomies' => '@inherit',
                'content::collections' => '@inherit',
                'content::navigation' => '@inherit',
                'content::assets' => '@inherit',
            ],
        ])->get('Content')->map->display()->all());

        // With full nesting of sections...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'sections' => [
                'content' => [
                    'reorder' => true,
                    'items' => [
                        'content::globals' => '@inherit',
                        'content::taxonomies' => '@inherit',
                        'content::collections' => '@inherit',
                        'content::navigation' => '@inherit',
                        'content::assets' => '@inherit',
                    ],
                ],
            ],
        ])->get('Content')->map->display()->all());

        // Merge unmentioned items underneath...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => true,
                'items' => [
                    'content::globals' => '@inherit',
                    'content::taxonomies' => '@inherit',
                    'content::collections' => '@inherit',
                ],
            ],
        ])->get('Content')->map->display()->all());

        // Ensure re-ordering items still works when modifying a item...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => true,
                'items' => [
                    'content::globals' => '@inherit',
                    'content::taxonomies' => [
                        'icon' => 'tag',
                    ],
                    'content::collections' => '@inherit',
                ],
            ],
        ])->get('Content')->map->display()->all());

        // If `reorder: false`, it should just use default item order...
        $this->assertEquals($defaultContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => false,
                'items' => [
                    'content::globals' => '@inherit',
                    'content::taxonomies' => '@inherit',
                    'content::collections' => '@inherit',
                    'content::navigation' => '@inherit',
                    'content::assets' => '@inherit',
                ],
            ],
        ])->get('Content')->map->display()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals($defaultContentItems, $this->buildNavWithPreferences([
            'content' => [
                'items' => [
                    'content::globals' => '@inherit',
                    'content::taxonomies' => '@inherit',
                    'content::collections' => '@inherit',
                    'content::navigation' => '@inherit',
                    'content::assets' => '@inherit',
                ],
            ],
        ])->get('Content')->map->display()->all());
    }

    /** @test */
    public function it_does_nothing_with_inherit_actions_when_not_reordering()
    {
        $nav = $this->buildNavWithPreferences([
            'sections' => [
                'fields' => '@inherit',
                'users' => [
                    'items' => [
                        'users::users' => '@inherit',
                        'top_level::dashboard' => '@inherit',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(['Dashboard'], $nav->get('Top Level')->map->display()->all());
        $this->assertEquals(['Blueprints', 'Fieldsets'], $nav->get('Fields')->map->display()->all());
        $this->assertEquals(['Users', 'Groups', 'Permissions'], $nav->get('Users')->map->display()->all());
    }

    /** @test */
    public function it_can_rename_sections()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $renamedSections = ['Top Level', 'Data', 'Fields', 'Tools', 'Pals'];

        // Recommended syntax...
        $this->assertEquals($renamedSections, $this->buildNavWithPreferences([
            'content' => [
                'display' => 'Data',
            ],
            'users' => [
                'display' => 'Pals',
            ],
        ])->keys()->all());

        // With nesting...
        $this->assertEquals($renamedSections, $this->buildNavWithPreferences([
            'sections' => [
                'content' => [
                    'display' => 'Data',
                ],
                'users' => [
                    'display' => 'Pals',
                ],
            ],
        ])->keys()->all());

        // Ensure renamed sections still hold original items...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'display' => 'Data',
            ],
            'users' => [
                'display' => 'Pals',
            ],
        ]);
        $this->assertNull($nav->get('Content'));
        $this->assertEquals(['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'], $nav->get('Data')->map->display()->all());
        $this->assertNull($nav->get('Users'));
        $this->assertEquals(['Users', 'Groups', 'Permissions'], $nav->get('Pals')->map->display()->all());
    }

    /** @test */
    public function it_can_rename_items_within_a_section()
    {
        $defaultItems = ['Users', 'Groups', 'Permissions'];

        $this->assertEquals($defaultItems, $this->buildDefaultNav()->get('Users')->map->display()->all());

        $renamedItems = ['Kids', 'Groups', 'Kid Can Haz?'];

        // Recommended syntax...
        $this->assertEquals($renamedItems, $this->buildNavWithPreferences([
            'users' => [
                'users::users' => [
                    'display' => 'Kids',
                ],
                'users::permissions' => [
                    'display' => 'Kid Can Haz?',
                ],
            ],
        ])->get('Users')->map->display()->all());

        // With nesting...
        $this->assertEquals($renamedItems, $this->buildNavWithPreferences([
            'sections' => [
                'users' => [
                    'items' => [
                        'users::users' => [
                            'display' => 'Kids',
                        ],
                        'users::permissions' => [
                            'display' => 'Kid Can Haz?',
                        ],
                    ],
                ],
            ],
        ])->get('Users')->map->display()->all());

        // Ensure renamed items still hold original child items...
        Facades\Collection::make('articles')->title('Articles')->save();
        Facades\Collection::make('pages')->title('Pages')->save();
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections' => [
                    'display' => 'Things',
                ],
            ],
        ]);
        $this->assertEquals(['Articles', 'Pages'], $nav->get('Content')->keyBy->display()->get('Things')->resolveChildren()->children()->map->display()->all());
    }

    /** @test */
    public function it_can_alias_items_into_another_section()
    {
        $this->assertEquals(['Dashboard'], $this->buildDefaultNav()->get('Top Level')->map->display()->all());

        // Recommended syntax...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => '@alias',
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With nesting...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'items' => [
                    'fields::blueprints' => '@alias',
                ],
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With full nesting of sections...
        $nav = $this->buildNavWithPreferences([
            'sections' => [
                'top_level' => [
                    'fields::blueprints' => [
                        'action' => '@alias',
                    ],
                ],
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With config array...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => [
                    'action' => '@alias',
                ],
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With implicit action (items from other sections default to `@alias`)...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => [
                    'action' => [],
                ],
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // Alias into another section...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'content::globals' => '@alias',
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Globals'], $nav->get('Fields')->map->display()->all());
        $this->assertArrayHasKey('Globals', $nav->get('Content')->keyBy->display()->all());

        // Alias a child item...
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections::pages' => '@alias',
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Pages'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());

        // Aliasing in same section should just copy the item...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'fields::blueprints' => '@alias',
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Blueprints'], $nav->get('Fields')->map->display()->all());
    }

    /** @test */
    public function it_can_move_items_into_another_section()
    {
        $this->assertEquals(['Dashboard'], $this->buildDefaultNav()->get('Top Level')->map->display()->all());

        // Recommended syntax...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => '@move',
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayNotHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With nesting...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'items' => [
                    'fields::blueprints' => '@move',
                ],
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayNotHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With config array...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => [
                    'action' => '@move',
                ],
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Blueprints'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayNotHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // Move into another section...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'content::globals' => '@move',
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Globals'], $nav->get('Fields')->map->display()->all());
        $this->assertArrayNotHasKey('Globals', $nav->get('Content')->keyBy->display()->all());

        // Move a child item...
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections::pages' => '@move',
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Pages'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayNotHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());

        // Move should do nothing if used in same section...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'fields::blueprints' => '@move',
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets'], $nav->get('Fields')->map->display()->all());
    }

    /** @test */
    public function it_can_remove_items_from_a_section()
    {
        $defaultContentItems = ['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'];

        $this->assertEquals($defaultContentItems, $this->buildDefaultNav()->get('Content')->map->display()->all());

        $itemsAfterRemoving = ['Collections', 'Taxonomies', 'Assets'];

        // Recommended syntax...
        $this->assertEquals($itemsAfterRemoving, $this->buildNavWithPreferences([
            'content' => [
                'content::navigation' => '@remove',
                'content::globals' => '@remove',
            ],
        ])->get('Content')->map->display()->all());

        // With nesting...
        $this->assertEquals($itemsAfterRemoving, $this->buildNavWithPreferences([
            'sections' => [
                'content' => [
                    'items' => [
                        'content::navigation' => '@remove',
                        'content::globals' => '@remove',
                    ],
                ],
            ],
        ])->get('Content')->map->display()->all());

        // With config array...
        $this->assertEquals($itemsAfterRemoving, $this->buildNavWithPreferences([
            'content' => [
                'content::navigation' => [
                    'action' => '@remove',
                ],
                'content::globals' => '@remove',
            ],
        ])->get('Content')->map->display()->all());

        // Remove a child item...
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections::pages' => '@remove',
            ],
        ]);
        $this->assertArrayNotHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());

        // Remove should do nothing if used in wrong section...
        $this->assertEquals($defaultContentItems, $this->buildNavWithPreferences([
            'fields' => [
                'content::navigation' => '@remove',
                'content::globals' => '@remove',
            ],
        ])->get('Content')->map->display()->all());
    }

    /** @test */
    public function it_can_modify_existing_items()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function modifying_moved_or_aliased_items_only_modifies_the_clone_and_not_the_original()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_can_create_new_items_on_the_fly()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_can_handle_a_bunch_of_useless_config_without_erroring()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_builds_out_an_example_config()
    {
        $this->markTestSkipped();
    }

    private function buildNavWithPreferences($preferences)
    {
        // Swap with fakes instead of using mocks,
        // because a mock can only set one set of expectations per test method...
        Facades\Preference::swap(new FakePreferences($preferences));
        Facades\CP\Nav::swap(new Nav);

        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());

        return Facades\CP\Nav::build();
    }

    private function buildDefaultNav()
    {
        return $this->buildNavWithPreferences([]);
    }
}

class FakePreferences
{
    private $preferences;

    public function __construct($preferences)
    {
        $this->preferences = $preferences;
    }

    public function get()
    {
        return $this->preferences;
    }
}

    // // Recommended syntax...
    // $usersNav = $this->buildNavWithPreferences([
    //     'users' => [
    //         'users::users' => [
    //             'display' => 'Kids',
    //             'url' => '/kids',
    //         ],
    //         'users::permissions' => [
    //             'display' => 'Kid Can Haz?',
    //             'icon' => '<svg>custom</svg>',
    //         ],
    //     ],
    // ])->get('Users');
    // $this->assertEquals($renamedItems, $usersNav->map->display()->all());
    // $this->assertEquals('http://localhost/kids', $usersNav->keyBy->display()->get('Kids')->url());
    // $this->assertEquals('<svg>custom</svg>', $usersNav->keyBy->display()->get('Kid Can Haz?')->icon());
