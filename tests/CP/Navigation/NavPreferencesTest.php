<?php

namespace Tests\CP\Navigation;

use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NavPreferencesTest extends TestCase
{
    use Concerns\HashedIdAssertions;
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        // TODO: Other tests are leaving behind forms without titles that are causing failures here?
        Facades\Form::shouldReceive('all')->andReturn(collect());
    }

    #[Test]
    public function it_can_reorder_sections()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $reorderedSections = ['Top Level', 'Users', 'Fields', 'Content', 'Tools', 'Settings'];

        // Recommended syntax...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => [
                'users',
                'fields',
                'content',
                'tools',
                'settings',
            ],
        ])->keys()->all());

        // Merge unmentioned sections underneath...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => [
                'users',
                'fields',
            ],
        ])->keys()->all());

        // Always merge top level section at top, even when explicitly defining in middle...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => [
                'users',
                'top_level',
                'fields',
            ],
        ])->keys()->all());

        // Ensure re-ordering sections still works when modifying a section...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => [
                'users',
                'fields',
            ],
            'sections' => [
                'fields' => [
                    'items' => [
                        'top_level::dashboard' => '@alias',
                    ],
                ],
            ],
        ])->keys()->all());

        // If `reorder: false`, it should just use default section order...
        $this->assertEquals($defaultSections, $this->buildNavWithPreferences([
            'reorder' => false,
        ])->keys()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals($defaultSections, $this->buildNavWithPreferences([])->keys()->all());
    }

    #[Test]
    public function it_can_still_reorder_sections_using_legacy_reorder_boolean_with_inherits()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $reorderedSections = ['Top Level', 'Users', 'Fields', 'Content', 'Tools', 'Settings'];

        // Recommended syntax...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
                'settings' => '@inherit',
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

    #[Test]
    public function it_handles_created_and_renamed_sections_properly_when_reordering()
    {
        $expected = [
            'Top Level',
            'Users',
            'Fieldios',
            'New Section',
            'Content',
            'Tools',
            'Settings',
            'New Section At End',
        ];

        $this->assertEquals($expected, $this->buildNavWithPreferences([
            'reorder' => [
                'users',
                'fields',
                'new_section', // We want to explicitly show this section early in the order
                // 'new_section_at_end', // But let this one generate as an appended section at the end
            ],
            'sections' => [
                'fields' => [
                    'display' => 'Fieldios', // Even though this section has been renamed, it should still reorder using original section id
                    'items' => [
                        'top_level::dashboard' => '@alias',
                    ],
                ],
                'new_section' => [
                    'items' => [
                        'top_level::dashboard' => '@alias',
                    ],
                ],
                'new_section_at_end' => [
                    'items' => [
                        'top_level::dashboard' => '@alias',
                    ],
                ],
            ],
        ])->keys()->all());
    }

    #[Test]
    public function it_can_reorder_items_within_sections()
    {
        $defaultContentItems = ['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'];

        $this->assertEquals($defaultContentItems, $this->buildDefaultNav()->get('Content')->map->display()->all());

        $reorderedContentItems = ['Globals', 'Taxonomies', 'Collections', 'Navigation', 'Assets'];

        // Recommended syntax...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => [
                    'content::globals',
                    'content::taxonomies',
                    'content::collections',
                    'content::navigation',
                    'content::assets',
                ],
            ],
        ])->get('Content')->map->display()->all());

        // With full nesting of sections...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'sections' => [
                'content' => [
                    'reorder' => [
                        'content::globals',
                        'content::taxonomies',
                        'content::collections',
                        'content::navigation',
                        'content::assets',
                    ],
                ],
            ],
        ])->get('Content')->map->display()->all());

        // Merge unmentioned items underneath...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => [
                    'content::globals',
                    'content::taxonomies',
                    'content::collections',
                ],
            ],
        ])->get('Content')->map->display()->all());

        // Ensure re-ordering items still works when modifying a item...
        $this->assertEquals($reorderedContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => [
                    'content::globals',
                    'content::taxonomies',
                    'content::collections',
                ],
                'items' => [
                    'content::taxonomies' => [
                        'icon' => 'tag',
                    ],
                ],
            ],
        ])->get('Content')->map->display()->all());

        // If `reorder: false`, it should just use default item order...
        $this->assertEquals($defaultContentItems, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => false,
            ],
        ])->get('Content')->map->display()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals($defaultContentItems, $this->buildNavWithPreferences([])->get('Content')->map->display()->all());
    }

    #[Test]
    public function it_can_still_reorder_items_within_sections_using_legacy_reorder_boolean_with_inherits()
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

    #[Test]
    public function it_handles_created_and_renamed_items_within_sections_properly_when_reordering()
    {
        $expected = [
            'Collections',
            'Taxonomeees',
            'New Item',
            'Assets',
            'Globals',
            'New Item At End',
        ];

        $this->assertEquals($expected, $this->buildNavWithPreferences([
            'content' => [
                'reorder' => [
                    'content::collections',
                    'content::taxonomies',
                    'content::new_item', // We want to explicitly show this section item early in the order
                    // 'content::new_item_at_end', // But let this one generate as an appended section at the end
                ],
                'items' => [
                    'content::taxonomies' => [
                        'action' => '@modify',
                        'display' => 'Taxonomeees', // Even though this item has been renamed, it should still reorder using original item id
                    ],
                    'content::navigation' => '@hide', // Hide this, why not
                    'content::new_item' => [
                        'action' => '@create',
                        'display' => 'New Item',
                    ],
                    'content::new_item_at_end' => [
                        'action' => '@create',
                        'display' => 'New Item At End',
                    ],
                ],
            ],
        ])->get('Content')->map->display()->all());
    }

    #[Test]
    public function it_can_reorder_child_items_within_an_item()
    {
        $defaultUtilitiesItems = ['Cache', 'Email', 'Licensing', 'PHP Info', 'Search'];

        $this->assertEquals($defaultUtilitiesItems, $this->buildDefaultNav()->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        $reorderedUtilitiesItems = ['Search', 'Cache', 'Licensing', 'Email', 'PHP Info'];

        // Recommended syntax...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => [
                        'tools::utilities::search',
                        'tools::utilities::cache',
                        'tools::utilities::licensing',
                        'tools::utilities::email',
                        'tools::utilities::php_info',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // With full nesting of sections...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'sections' => [
                'tools' => [
                    'tools::utilities' => [
                        'reorder' => [
                            'tools::utilities::search',
                            'tools::utilities::cache',
                            'tools::utilities::licensing',
                            'tools::utilities::email',
                            'tools::utilities::php_info',
                        ],
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // Merge unmentioned items underneath...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => [
                        'tools::utilities::search',
                        'tools::utilities::cache',
                        'tools::utilities::licensing',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // Ensure re-ordering items still works when modifying a item...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => [
                        'tools::utilities::search',
                        'tools::utilities::cache',
                        'tools::utilities::licensing',
                    ],
                    'children' => [
                        'tools::utilities::cache' => [
                            'action' => '@modify',
                            'url' => '/cache-money',
                        ],
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // If `reorder: false`, it should just use default item order...
        $this->assertEquals($defaultUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => false,
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals(
            $defaultUtilitiesItems,
            $this->buildNavWithPreferences([])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all(),
        );
    }

    #[Test]
    public function it_can_still_reorder_child_items_within_an_item_using_legacy_reorder_boolean_with_inherits()
    {
        $defaultUtilitiesItems = ['Cache', 'Email', 'Licensing', 'PHP Info', 'Search'];

        $this->assertEquals($defaultUtilitiesItems, $this->buildDefaultNav()->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        $reorderedUtilitiesItems = ['Search', 'Cache', 'Licensing', 'Email', 'PHP Info'];

        // Recommended syntax...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => true,
                    'children' => [
                        'tools::utilities::search' => '@inherit',
                        'tools::utilities::cache' => '@inherit',
                        'tools::utilities::licensing' => '@inherit',
                        'tools::utilities::email' => '@inherit',
                        'tools::utilities::php_info' => '@inherit',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // With full nesting of sections...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'sections' => [
                'tools' => [
                    'tools::utilities' => [
                        'reorder' => true,
                        'children' => [
                            'tools::utilities::search' => '@inherit',
                            'tools::utilities::cache' => '@inherit',
                            'tools::utilities::licensing' => '@inherit',
                            'tools::utilities::email' => '@inherit',
                            'tools::utilities::php_info' => '@inherit',
                        ],
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // Merge unmentioned items underneath...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => true,
                    'children' => [
                        'tools::utilities::search' => '@inherit',
                        'tools::utilities::cache' => '@inherit',
                        'tools::utilities::licensing' => '@inherit',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // Ensure re-ordering items still works when modifying a item...
        $this->assertEquals($reorderedUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => true,
                    'children' => [
                        'tools::utilities::search' => '@inherit',
                        'tools::utilities::cache' => [
                            'url' => '/cache-money',
                        ],
                        'tools::utilities::licensing' => '@inherit',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // If `reorder: false`, it should just use default item order...
        $this->assertEquals($defaultUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => false,
                    'children' => [
                        'tools::utilities::search' => '@inherit',
                        'tools::utilities::cache' => '@inherit',
                        'tools::utilities::licensing' => '@inherit',
                        'tools::utilities::email' => '@inherit',
                        'tools::utilities::php_info' => '@inherit',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals($defaultUtilitiesItems, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'children' => [
                        'tools::utilities::search' => '@inherit',
                        'tools::utilities::cache' => '@inherit',
                        'tools::utilities::licensing' => '@inherit',
                        'tools::utilities::email' => '@inherit',
                        'tools::utilities::php_info' => '@inherit',
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());
    }

    #[Test]
    public function it_handles_created_and_renamed_child_items_properly_when_reordering()
    {
        $expected = [
            'Search',
            'Cache Moneys!',
            'New Child Item',
            'Email',
            'Licensing',
            'PHP Info',
            'New Child Item At End',
        ];

        $this->assertEquals($expected, $this->buildNavWithPreferences([
            'tools' => [
                'tools::utilities' => [
                    'reorder' => [
                        'tools::utilities::search',
                        'tools::utilities::cache',
                        'tools::utilities::new_item', // We want to explicitly show this section item early in the order
                        // 'tools::utilities::new_item_at_end', // But let this one generate as an appended section at the end
                    ],
                    'children' => [
                        'tools::utilities::cache' => [
                            'action' => '@modify',
                            'display' => 'Cache Moneys!', // Even though this item has been renamed, it should still reorder using original item id
                        ],
                        'tools::utilities::new_item' => [
                            'action' => '@create',
                            'display' => 'New Child Item',
                        ],
                        'tools::utilities::new_item_at_end' => [
                            'action' => '@create',
                            'display' => 'New Child Item At End',
                        ],
                    ],
                ],
            ],
        ])->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->map->display()->all());
    }

    #[Test]
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

    #[Test]
    public function it_can_rename_sections()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $renamedSections = ['Top Level', 'Data', 'Fields', 'Tools', 'Settings', 'Pals'];

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

    #[Test]
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
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections' => [
                    'display' => 'Things',
                ],
            ],
        ]);
        $this->assertEquals(['Articles', 'Pages'], $nav->get('Content')->keyBy->display()->get('Things')->resolveChildren()->children()->map->display()->all());
    }

    #[Test]
    public function it_can_alias_items_within_a_section()
    {
        $this->assertEquals(['Blueprints', 'Fieldsets'], $this->buildDefaultNav()->get('Fields')->map->display()->all());

        // Recommended syntax...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'fields::blueprints' => '@alias',
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Blueprints'], $nav->get('Fields')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With nesting...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'items' => [
                    'fields::blueprints' => '@alias',
                ],
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Blueprints'], $nav->get('Fields')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With full nesting of sections...
        $nav = $this->buildNavWithPreferences([
            'sections' => [
                'fields' => [
                    'fields::blueprints' => [
                        'action' => '@alias',
                    ],
                ],
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Blueprints'], $nav->get('Fields')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // With config array...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'fields::blueprints' => [
                    'action' => '@alias',
                ],
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Blueprints'], $nav->get('Fields')->map->display()->all());
        $this->assertArrayHasKey('Blueprints', $nav->get('Fields')->keyBy->display()->all());

        // Alias a child item...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections::pages' => '@alias',
            ],
        ]);
        $this->assertEquals(['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals', 'Pages'], $nav->get('Content')->map->display()->all());
        $this->assertArrayHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->keyBy->display()->all());
    }

    #[Test]
    public function aliasing_an_item_does_not_include_its_children()
    {
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections' => '@alias',
            ],
        ]);

        $this->assertNull($nav->get('Top Level')->keyBy->display()->get('Collections')->resolveChildren()->children());
        $this->assertCount(2, $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->all());
    }

    #[Test]
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
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections::pages' => '@alias',
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Pages'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->keyBy->display()->all());

        // Aliasing in same section should just copy the item...
        $nav = $this->buildNavWithPreferences([
            'fields' => [
                'fields::blueprints' => '@alias',
            ],
        ]);
        $this->assertEquals(['Blueprints', 'Fieldsets', 'Blueprints'], $nav->get('Fields')->map->display()->all());
    }

    #[Test]
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
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections::pages' => '@move',
            ],
        ]);
        $this->assertEquals(['Dashboard', 'Pages'], $nav->get('Top Level')->map->display()->all());
        $this->assertArrayNotHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());
    }

    #[Test]
    public function it_can_hide_sections()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $sectionsAfterHiding = ['Top Level', 'Fields', 'Tools', 'Settings'];

        // Recommended syntax...
        $this->assertEquals($sectionsAfterHiding, $this->buildNavWithPreferences([
            'content' => '@hide',
            'users' => '@hide',
        ])->keys()->all());

        // With nesting...
        $this->assertEquals($sectionsAfterHiding, $this->buildNavWithPreferences([
            'sections' => [
                'content' => '@hide',
                'users' => '@hide',
            ],
        ])->keys()->all());

        // With config array...
        $this->assertEquals($sectionsAfterHiding, $this->buildNavWithPreferences([
            'content' => [
                'action' => '@hide',
            ],
            'users' => [
                'action' => '@hide',
            ],
        ])->keys()->all());
    }

    #[Test]
    public function it_can_hide_items_from_a_section()
    {
        $defaultContentItems = ['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'];

        $this->assertEquals($defaultContentItems, $this->buildDefaultNav()->get('Content')->map->display()->all());

        $itemsAfterHiding = ['Collections', 'Taxonomies', 'Assets'];

        // Recommended syntax...
        $this->assertEquals($itemsAfterHiding, $this->buildNavWithPreferences([
            'content' => [
                'content::navigation' => '@hide',
                'content::globals' => '@hide',
            ],
        ])->get('Content')->map->display()->all());

        // With nesting...
        $this->assertEquals($itemsAfterHiding, $this->buildNavWithPreferences([
            'sections' => [
                'content' => [
                    'items' => [
                        'content::navigation' => '@hide',
                        'content::globals' => '@hide',
                    ],
                ],
            ],
        ])->get('Content')->map->display()->all());

        // With config array...
        $this->assertEquals($itemsAfterHiding, $this->buildNavWithPreferences([
            'content' => [
                'content::navigation' => [
                    'action' => '@hide',
                ],
                'content::globals' => '@hide',
            ],
        ])->get('Content')->map->display()->all());

        // Hide a child item...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections::pages' => '@hide',
            ],
        ]);
        $this->assertArrayNotHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());
        $this->assertArrayHasKey('Articles', $nav->get('Content')->keyBy->display()->get('Collections')->children()->keyBy->display()->all());

        // Hide should do nothing if used in wrong section...
        $this->assertEquals($defaultContentItems, $this->buildNavWithPreferences([
            'fields' => [
                'content::navigation' => '@hide',
                'content::globals' => '@hide',
            ],
        ])->get('Content')->map->display()->all());
    }

    #[Test]
    public function it_can_create_new_items_on_the_fly()
    {
        // It can create items in child items...
        $item = $this->buildNavWithPreferences([
            'top_level' => [
                'favs' => [
                    'action' => '@create',
                    'display' => 'Favourites',
                    'url' => 'https://pinterest.com',
                    'icon' => '<svg>custom</svg>',
                    'children' => [
                        'One' => '/one',
                        'two' => [
                            'action' => '@create',
                            'display' => 'Two',
                            'url' => '/two',
                        ],
                    ],
                ],
            ],
        ])->get('Top Level')->keyBy->display()->get('Favourites');
        $this->assertEquals('top_level::favourites', $item->id());
        $this->assertEquals('Favourites', $item->display());
        $this->assertEquals('https://pinterest.com', $item->url());
        $this->assertEquals('<svg>custom</svg>', $item->icon());
        $this->assertEquals(['top_level::favourites::one', 'top_level::favourites::two'], $item->children()->map->id()->all());
        $this->assertEquals(['One', 'Two'], $item->children()->map->display()->all());
        $this->assertEquals(['http://localhost/one', 'http://localhost/two'], $item->children()->map->url()->all());

        // It can merged created children into existing children...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'Json' => 'https://json.org',
                        'spaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://yaml.org',
                        ],
                    ],
                ],
            ],
        ]);
        $modifiedItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles', 'Pages', 'Json', 'Yaml'], $modifiedItem->children()->map->display()->all());
        $this->assertEquals([
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
            'https://json.org',
            'https://yaml.org',
        ], $modifiedItem->children()->map->url()->all());

        // It can merged created children into existing children of a moved item...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections' => [
                    'action' => '@move',
                    'children' => [
                        'Json' => 'https://json.org',
                        'spaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://yaml.org',
                        ],
                    ],
                ],
            ],
        ]);
        $movedItem = $nav->get('Top Level')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles', 'Pages', 'Json', 'Yaml'], $movedItem->resolveChildren()->children()->map->name()->all());
        $this->assertEquals([
            'content::collections::articles',
            'content::collections::pages',
            'content::collections::json',
            'content::collections::yaml',
        ], $movedItem->children()->map->id()->all());
        $this->assertEquals([
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
            'https://json.org',
            'https://yaml.org',
        ], $movedItem->children()->map->url()->all());

        // Aliased items don't have children by default, but we can still create child items for them...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections' => [
                    'action' => '@alias',
                    'children' => [
                        'Json' => 'https://json.org',
                        'spaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://yaml.org',
                        ],
                    ],
                ],
            ],
        ]);
        $originalItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $aliasedItem = $nav->get('Top Level')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles', 'Pages'], $originalItem->resolveChildren()->children()->map->name()->all());
        $aliasedItemId = $this->assertIsHashedIdFor('content::collections', $aliasedItem->id());
        $this->assertEquals([
            "{$aliasedItemId}::json",
            "{$aliasedItemId}::yaml",
        ], $aliasedItem->children()->map->id()->all());
        $this->assertEquals(['Json', 'Yaml'], $aliasedItem->children()->map->display()->all());
        $this->assertEquals([
            'https://json.org',
            'https://yaml.org',
        ], $aliasedItem->children()->map->url()->all());

        // It can create using `route` setter...
        $this->assertEquals('http://localhost/cp/dashboard', $this->buildNavWithPreferences([
            'top_level' => [
                'favs' => [
                    'action' => '@create',
                    'display' => 'Favourites',
                    'route' => 'dashboard',
                ],
            ],
        ])->get('Top Level')->keyBy->display()->get('Favourites')->url());

        // It won't create without a `display` setter at minimum...
        $this->assertEquals(['Dashboard'], $this->buildNavWithPreferences([
            'top_level' => [
                'favs' => [
                    'action' => '@create',
                ],
            ],
        ])->get('Top Level')->map->display()->all());
    }

    #[Test]
    public function it_can_modify_existing_items()
    {
        // It can modify item within a section...
        $item = $this->buildNavWithPreferences([
            'top_level' => [
                'top_level::dashboard' => [
                    'action' => '@modify',
                    'display' => 'Dashboard Confessional',
                    'url' => 'https://dashboardconfessional.com',
                    'icon' => '<svg>custom</svg>',
                    'children' => [
                        'One' => '/one',
                        'Two' => '/two',
                    ],
                ],
            ],
        ])->get('Top Level')->keyBy->display()->get('Dashboard Confessional');
        $this->assertEquals('top_level::dashboard', $item->id());
        $this->assertEquals('Dashboard Confessional', $item->display());
        $this->assertEquals('https://dashboardconfessional.com', $item->url());
        $this->assertEquals('<svg>custom</svg>', $item->icon());
        $this->assertEquals(['top_level::dashboard::one', 'top_level::dashboard::two'], $item->children()->map->id()->all());
        $this->assertEquals(['One', 'Two'], $item->children()->map->display()->all());
        $this->assertEquals(['http://localhost/one', 'http://localhost/two'], $item->children()->map->url()->all());

        // It can modify an aliased item...
        $item = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => [
                    'action' => '@alias',
                    'display' => 'Redprints',
                    'url' => 'https://redprints.com',
                    'icon' => '<svg>custom</svg>',
                    'children' => [
                        'One' => '/one',
                        'Two' => '/two',
                    ],
                ],
            ],
        ])->get('Top Level')->keyBy->display()->get('Redprints');
        $this->assertIsHashedIdFor('fields::blueprints', $blueprintsId = $item->id());
        $this->assertEquals('Redprints', $item->display());
        $this->assertEquals('https://redprints.com', $item->url());
        $this->assertEquals('<svg>custom</svg>', $item->icon());
        $this->assertEquals(["{$blueprintsId}::one", "{$blueprintsId}::two"], $item->children()->map->id()->all());
        $this->assertEquals(['One', 'Two'], $item->children()->map->display()->all());
        $this->assertEquals(['http://localhost/one', 'http://localhost/two'], $item->children()->map->url()->all());

        // It can modify a moved item...
        $item = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => [
                    'action' => '@move',
                    'display' => 'Redprints',
                    'url' => 'https://redprints.com',
                    'icon' => '<svg>custom</svg>',
                    'children' => [
                        'One' => '/one',
                        'Two' => '/two',
                    ],
                ],
            ],
        ])->get('Top Level')->keyBy->display()->get('Redprints');
        $this->assertEquals('fields::blueprints', $item->id());
        $this->assertEquals('Redprints', $item->display());
        $this->assertEquals('https://redprints.com', $item->url());
        $this->assertEquals('<svg>custom</svg>', $item->icon());
        $this->assertEquals(['fields::blueprints::one', 'fields::blueprints::two'], $item->children()->map->id()->all());
        $this->assertEquals(['One', 'Two'], $item->children()->map->display()->all());
        $this->assertEquals(['http://localhost/one', 'http://localhost/two'], $item->children()->map->url()->all());

        // It does not modify items from other sections... (instead, use `@alias` or `@move` action as shown above)
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'top_level::dashboard' => [
                    'action' => '@modify',
                    'display' => 'Dashboard Confessional',
                ],
            ],
        ]);
        $this->assertArrayHasKey('Dashboard', $nav->get('Top Level')->keyBy->display()->all());
        $this->assertArrayNotHasKey('Dashboard Confessional', $nav->get('Top Level')->keyBy->display()->all());
        $this->assertArrayNotHasKey('Dashboard Confessional', $nav->get('Content')->keyBy->display()->all());
    }

    #[Test]
    public function modifying_an_aliased_item_only_modifies_the_clone_and_not_the_original()
    {
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => [
                    'action' => '@alias',
                    'display' => 'Redprints',
                    'url' => 'https://redprints.com',
                ],
            ],
        ]);

        // Assert the cloned item...
        $this->assertIsHashedIdFor('fields::blueprints', $nav->get('Top Level')->keyBy->display()->get('Redprints')->id());
        $this->assertEquals('Redprints', $nav->get('Top Level')->keyBy->display()->get('Redprints')->display());
        $this->assertEquals('https://redprints.com', $nav->get('Top Level')->keyBy->display()->get('Redprints')->url());

        // Assert the original item...
        $this->assertEquals('fields::blueprints', $nav->get('Fields')->keyBy->display()->get('Blueprints')->id());
        $this->assertEquals('Blueprints', $nav->get('Fields')->keyBy->display()->get('Blueprints')->display());
        $this->assertEquals('http://localhost/cp/fields/blueprints', $nav->get('Fields')->keyBy->display()->get('Blueprints')->url());
    }

    #[Test]
    public function it_can_create_child_items_using_array_setter_notation()
    {
        $children = $this->buildNavWithPreferences([
            'top_level' => [
                'top_level::dashboard' => [
                    'action' => '@modify',
                    'children' => [
                        'json' => [
                            'action' => '@create',
                            'display' => 'Json',
                            'url' => 'https://json.org',
                        ],
                        'yaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://yaml.org',
                            'children' => ['One' => '/one'], // Children of children should get filtered out
                        ],
                        'toml' => ['action' => '@create'], // Items without `display` config should get filtered out
                    ],
                ],
            ],
        ])->get('Top Level')->keyBy->display()->get('Dashboard')->children();

        $this->assertCount(2, $children);

        $jsonItem = $children->first();
        $this->assertEquals('top_level::dashboard::json', $jsonItem->id());
        $this->assertEquals('Json', $jsonItem->display());
        $this->assertEquals('https://json.org', $jsonItem->url());
        $this->assertNull($jsonItem->children());

        $yamlItem = $children->last();
        $this->assertEquals('top_level::dashboard::yaml', $yamlItem->id());
        $this->assertEquals('Yaml', $yamlItem->display());
        $this->assertEquals('https://yaml.org', $yamlItem->url());
        $this->assertNull($yamlItem->children());
    }

    #[Test]
    public function it_can_alias_items_into_the_children_of_another_item()
    {
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'top_level::dashboard' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => '@alias',
                        'tools::utilities::cache' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $children = $nav->get('Top Level')->keyBy->display()->get('Dashboard')->children());

        $pagesItem = $children->first();
        $this->assertIsHashedIdFor('content::collections::pages', $pagesItem->id());
        $this->assertEquals('Pages', $pagesItem->display());
        $this->assertArrayHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->keyBy->display()->all());

        $cacheItem = $children->last();
        $this->assertIsHashedIdFor('tools::utilities::cache', $cacheItem->id());
        $this->assertEquals('Cache', $cacheItem->display());
        $this->assertArrayHasKey('Cache', $nav->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->keyBy->display()->all());
    }

    #[Test]
    public function it_can_move_items_into_the_children_of_another_item()
    {
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'top_level::dashboard' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => '@move',
                        'tools::utilities::cache' => [
                            'action' => '@move',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $children = $nav->get('Top Level')->keyBy->display()->get('Dashboard')->children());

        $pagesItem = $children->first();
        $this->assertEquals('content::collections::pages', $pagesItem->id());
        $this->assertEquals('Pages', $pagesItem->display());
        $this->assertArrayNotHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->keyBy->display()->all());

        $cacheItem = $children->last();
        $this->assertEquals('tools::utilities::cache', $cacheItem->id());
        $this->assertEquals('Cache', $cacheItem->display());
        $this->assertArrayNotHasKey('Cache', $nav->get('Tools')->keyBy->display()->get('Utilities')->resolveChildren()->children()->keyBy->display()->all());
    }

    #[Test]
    public function it_can_move_items_out_of_the_children_of_an_item_in_the_same_section()
    {
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections::pages' => '@move',
            ],
        ]);

        $this->assertCount(1, $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->map->display()->all());

        $pagesItem = $nav->get('Content')->last();
        $this->assertEquals('content::collections::pages', $pagesItem->id());
        $this->assertEquals('Pages', $pagesItem->display());
        $this->assertArrayNotHasKey('Pages', $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children()->map->display()->all());
    }

    #[Test]
    public function it_can_move_child_items_into_another_items_children()
    {
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::taxonomies' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => '@move',
                    ],
                ],
            ],
        ]);

        $collectionsChildren = $nav->get('Content')->keyBy->display()->get('Collections')->resolveChildren()->children();
        $this->assertCount(1, $collectionsChildren);
        $this->assertEquals('content::collections::articles', $collectionsChildren->first()->id());
        $this->assertEquals('Articles', $collectionsChildren->first()->display());

        $taxonomiesChildren = $nav->get('Content')->keyBy->display()->get('Taxonomies')->resolveChildren()->children();
        $this->assertCount(1, $taxonomiesChildren);
        $this->assertEquals('content::collections::pages', $taxonomiesChildren->first()->id());
        $this->assertEquals('Pages', $taxonomiesChildren->first()->display());
    }

    #[Test]
    public function it_can_hide_child_items()
    {
        // When modifying parent...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => '@hide',
                    ],
                ],
            ],
        ]);
        $originalItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles'], $originalItem->resolveChildren()->children()->map->name()->all());
        $this->assertEquals(['content::collections::articles'], $originalItem->children()->map->id()->all());
        $this->assertEquals(['Articles'], $originalItem->children()->map->display()->all());
        $this->assertEquals(['http://localhost/cp/collections/articles'], $originalItem->children()->map->url()->all());

        // Without explicitly modifying parent...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections::pages' => '@hide',
            ],
        ]);
        $originalItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles'], $originalItem->resolveChildren()->children()->map->name()->all());
        $this->assertEquals(['content::collections::articles'], $originalItem->children()->map->id()->all());
        $this->assertEquals(['Articles'], $originalItem->children()->map->display()->all());
        $this->assertEquals(['http://localhost/cp/collections/articles'], $originalItem->children()->map->url()->all());

        // When moving parent...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections' => [
                    'action' => '@move',
                    'children' => [
                        'content::collections::pages' => '@hide',
                    ],
                ],
            ],
        ]);
        $this->assertNull($nav->get('Content')->keyBy->display()->get('Collections'));
        $movedItem = $nav->get('Top Level')->keyBy->display()->get('Collections');
        $this->assertEquals(['content::collections::articles'], $movedItem->children()->map->id()->all());
        $this->assertEquals(['Articles'], $movedItem->children()->map->display()->all());
        $this->assertEquals(['http://localhost/cp/collections/articles'], $movedItem->children()->map->url()->all());
    }

    #[Test]
    public function it_can_modify_existing_child_items()
    {
        // When modifying parent...
        $nav = $this->buildNavWithPreferences([
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => [
                            'action' => '@modify',
                            'display' => 'Pagerinos',
                        ],
                        'Json' => 'https://json.org',
                        'spaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://yaml.org',
                        ],
                    ],
                ],
            ],
        ]);
        $originalItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles', 'Pagerinos', 'Json', 'Yaml'], $originalItem->children()->map->display()->all());
        $this->assertEquals([
            'content::collections::articles',
            'content::collections::pages',
            'content::collections::json',
            'content::collections::yaml',
        ], $originalItem->children()->map->id()->all());
        $this->assertEquals([
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
            'https://json.org',
            'https://yaml.org',
        ], $originalItem->children()->map->url()->all());

        // Aliased items don't have children by default, but we can still alias and modify child items for them...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections' => [
                    'action' => '@alias',
                    'children' => [
                        'content::collections::pages' => [
                            'action' => '@alias',
                            'display' => 'Pagerinos',
                        ],
                    ],
                ],
            ],
        ]);
        $originalItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles', 'Pages'], $originalItem->resolveChildren()->children()->map->display()->all());
        $aliasedItem = $nav->get('Top Level')->keyBy->display()->get('Collections');
        $this->assertEquals(['Pagerinos'], $aliasedItem->children()->map->display()->all());
        $this->assertIsHashedIdFor('content::collections::pages', $aliasedItem->children()->first()->id());
        $this->assertEquals('http://localhost/cp/collections/pages', $aliasedItem->children()->first()->url());

        // When moving parent...
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections' => [
                    'action' => '@move',
                    'children' => [
                        'content::collections::pages' => [
                            'action' => '@modify',
                            'display' => 'Pagerinos',
                        ],
                        'Json' => 'https://json.org',
                        'spaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://yaml.org',
                        ],
                    ],
                ],
            ],
        ]);
        $originalItem = $nav->get('Content')->keyBy->display()->get('Collections');
        $this->assertNull($originalItem);
        $movedItem = $nav->get('Top Level')->keyBy->display()->get('Collections');
        $this->assertEquals(['Articles', 'Pagerinos', 'Json', 'Yaml'], $movedItem->children()->map->display()->all());
        $this->assertEquals([
            'content::collections::articles',
            'content::collections::pages',
            'content::collections::json',
            'content::collections::yaml',
        ], $movedItem->children()->map->id()->all());
        $this->assertEquals([
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
            'https://json.org',
            'https://yaml.org',
        ], $movedItem->children()->map->url()->all());
    }

    #[Test]
    public function it_can_alias_newly_created_items_to_an_earlier_section()
    {
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints::non_favourite' => '@alias', // Alias created child from a later modified item
                'tools::technologies::json' => '@alias', // Alias created child from a later created item
                'tools::technologies' => '@alias', // Alias later created item
            ],
            'fields' => [
                'fields::blueprints' => [
                    'action' => '@modify',
                    'children' => [
                        'Favourite' => '/fav',
                        'Non-Favourite' => '/non-fav',
                    ],
                ],
            ],
            'tools' => [
                'techs' => [
                    'action' => '@create',
                    'display' => 'Technologies',
                    'url' => '/techs',
                    'children' => [
                        'Json' => 'https://json.org',
                        'Yaml' => 'https://yaml.org',
                    ],
                ],
            ],
        ]);

        $favItem = $nav->get('Fields')->keyBy->display()->get('Blueprints')->children()->first();
        $this->assertEquals('fields::blueprints::favourite', $favItem->id());
        $this->assertEquals('Favourite', $favItem->display());
        $this->assertEquals('http://localhost/fav', $favItem->url());

        $nonFavItem = $nav->get('Fields')->keyBy->display()->get('Blueprints')->children()->last();
        $this->assertEquals('fields::blueprints::non_favourite', $nonFavItem->id());
        $this->assertEquals('Non-Favourite', $nonFavItem->display());
        $this->assertEquals('http://localhost/non-fav', $nonFavItem->url());

        $jsonItem = $nav->get('Tools')->keyBy->display()->get('Technologies')->children()->first();
        $this->assertEquals('tools::technologies::json', $jsonItem->id());
        $this->assertEquals('Json', $jsonItem->display());
        $this->assertEquals('https://json.org', $jsonItem->url());

        $yamlItem = $nav->get('Tools')->keyBy->display()->get('Technologies')->children()->last();
        $this->assertEquals('tools::technologies::yaml', $yamlItem->id());
        $this->assertEquals('Yaml', $yamlItem->display());
        $this->assertEquals('https://yaml.org', $yamlItem->url());

        $aliasedNonFavItem = $nav->get('Top Level')->keyBy->display()->get('Non-Favourite');
        $this->assertIsHashedIdFor('fields::blueprints::non_favourite', $aliasedNonFavItem->id());
        $this->assertEquals('Non-Favourite', $aliasedNonFavItem->display());
        $this->assertEquals('http://localhost/non-fav', $aliasedNonFavItem->url());

        $aliasedJsonItem = $nav->get('Top Level')->keyBy->display()->get('Json');
        $this->assertIsHashedIdFor('tools::technologies::json', $aliasedJsonItem->id());
        $this->assertEquals('Json', $aliasedJsonItem->display());
        $this->assertEquals('https://json.org', $aliasedJsonItem->url());

        $aliasedJsonItem = $nav->get('Top Level')->keyBy->display()->get('Technologies');
        $this->assertIsHashedIdFor('tools::technologies', $aliasedJsonItem->id());
        $this->assertEquals('Technologies', $aliasedJsonItem->display());
        $this->assertEquals('http://localhost/techs', $aliasedJsonItem->url());
    }

    #[Test]
    public function it_respects_order_that_items_are_aliased_and_created()
    {
        $items = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => '@move',
                'fields::fieldsets' => '@alias',
                'technologies' => [
                    'action' => '@create',
                    'display' => 'Technologies',
                    'children' => [
                        'Json' => 'https://json.org',
                        'Yaml' => 'https://yaml.org',
                    ],
                ],
            ],
        ])->get('Top Level')->map->display()->all();

        // Items are created first so that they can be aliased in earlier sections of the menu,
        // So we want to assert that they still get built in the same order that they are defined...
        $this->assertEquals(['Dashboard', 'Blueprints', 'Fieldsets', 'Technologies'], $items);
    }

    #[Test]
    public function preferences_are_applied_after_addon_nav_extensions()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children([
                    'Reports' => '/cp/seo-pro/reports',
                    'Site Defaults' => '/cp/seo-pro/site-defaults',
                    'Section Defaults' => '/cp/seo-pro/section-defaults',
                ]);
        });

        $nav = $this->buildNavWithPreferences([
            'sections' => [
                'top_level' => [
                    'tools::seo_pro' => '@alias',
                ],
            ],
        ]);

        // Assert addon successfully added nav item
        $this->assertEquals(['Forms', 'Updates', 'Addons', 'Utilities', 'GraphQL', 'SEO Pro'], $nav->get('Tools')->map->display()->all());

        // Assert preferences are applied after the fact, and can alias the addon's nav item
        $this->assertEquals(['Dashboard', 'SEO Pro'], $nav->get('Top Level')->map->display()->all());
    }

    #[Test]
    public function it_can_rename_extended_sections_that_dont_follow_title_case_convention()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->name('SEO Settings')
                ->section('SEO pro') // This being two words with 'pro' not being title case, caused a bug where preferences weren't properly applied
                ->url('/cp/seo-pro');
        });

        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO pro'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $renamedSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO Pro Renamed'];

        $this->assertEquals($renamedSections, $this->buildNavWithPreferences([
            'seo_pro' => [
                'display' => 'SEO Pro Renamed',
            ],
        ])->keys()->all());
    }

    #[Test]
    public function it_can_hide_extended_sections_that_dont_follow_title_case_convention()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->name('SEO Settings')
                ->section('SEO pro') // This being two words with 'pro' not being title case, caused a bug where preferences weren't properly applied
                ->url('/cp/seo-pro')
                ->children([
                    'Reports' => '/cp/seo-pro/reports',
                    'Site Defaults' => '/cp/seo-pro/site-defaults',
                    'Section Defaults' => '/cp/seo-pro/section-defaults',
                ]);
        });

        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO pro'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $renamedSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users'];

        $this->assertEquals($renamedSections, $this->buildNavWithPreferences([
            'seo_pro' => [
                'action' => '@hide',
            ],
        ])->keys()->all());
    }

    #[Test]
    public function it_can_create_new_items_in_extended_sections_that_dont_follow_title_case_convention()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->name('SEO Settings')
                ->section('SEO pro') // This being two words with 'pro' not being title case, caused a bug where preferences weren't properly applied
                ->url('/cp/seo-pro');
        });

        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO pro'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $nav = $this->buildNavWithPreferences([
            'seo_pro' => [
                'seo_pro::new_item' => [
                    'action' => '@create',
                    'display' => 'New Item',
                    'url' => '/new-item',
                ],
            ],
        ]);

        $this->assertEquals(['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO pro'], $nav->keys()->all());
        $this->assertEquals(['SEO Settings', 'New Item'], $nav->get('SEO pro')->map->display()->all());
    }

    #[Test]
    public function it_hides_section_when_all_items_are_moved_out_of_section()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->name('SEO Settings')
                ->section('SEO Pro')
                ->url('/cp/seo-pro');
        });

        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO Pro'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'seo_pro::seo_settings' => '@move',
            ],
        ]);

        $navWithHidden = $this->buildNavWithPreferences([
            'top_level' => [
                'seo_pro::seo_settings' => '@move',
            ],
        ], true);

        // Since we moved the SEO item to top level, it should hide SEO section by default...
        $this->assertEquals(['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users'], $nav->keys()->all());

        // But still show empty section when `withHidden` flag is true, so that user can re-add items to this core/extended section...
        $this->assertEquals(['Top Level', 'Content', 'Fields', 'Tools', 'Settings', 'Users', 'SEO Pro'], $navWithHidden->keys()->all());
        $this->assertTrue($navWithHidden->get('SEO Pro')->isEmpty());
    }

    #[Test]
    public function it_builds_out_an_example_config()
    {
        $nav = $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'top_level' => [
                    'content::collections::pages' => '@alias',
                ],
                'tools' => '@inherit',
                'users' => [
                    'reorder' => true,
                    'items' => [
                        'users::permissions' => '@inherit',
                        'users::groups' => '@inherit',
                    ],
                ],
                'content' => [
                    'display' => 'Site',
                    'items' => [
                        'content::globals' => '@hide',
                        'content::taxonomies' => [
                            'action' => '@modify',
                            'display' => 'Categories',
                        ],
                        'fields::blueprints' => '@move',
                        'flickr' => [
                            'action' => '@create',
                            'icon' => 'assets',
                            'display' => 'Flickr',
                            'url' => 'https://flickr.com',
                            'children' => [
                                'Profile' => '/profile',
                                'edit' => [
                                    'action' => '@create',
                                    'display' => 'Edit',
                                    'url' => '/edit',
                                ],
                            ],
                        ],
                        'fields::fieldsets' => [
                            'action' => '@alias',
                            'url' => '/cp/fields/fieldsets?modified',
                        ],
                    ],
                ],
            ],
        ]);

        // Assert section order, with section rename from 'Content' to 'Site'
        $this->assertEquals(['Top Level', 'Tools', 'Users', 'Site', 'Fields', 'Settings'], $nav->keys()->all());

        // Assert top level items, with aliased 'Pages' item
        $this->assertEquals([
            'Dashboard' => 'http://localhost/cp/dashboard',
            'Pages' => 'http://localhost/cp/collections/pages',
        ], $nav->get('Top Level')->mapWithKeys(fn ($i) => [$i->display() => $i->url()])->all());

        // Assert tools items (untouched because `@inherit`)
        $this->assertEquals([
            'Forms' => 'http://localhost/cp/forms',
            'Updates' => 'http://localhost/cp/updater',
            'Addons' => 'http://localhost/cp/addons',
            'Utilities' => 'http://localhost/cp/utilities',
            'GraphQL' => 'http://localhost/cp/graphql',
        ], $nav->get('Tools')->mapWithKeys(fn ($i) => [$i->display() => $i->url()])->all());

        // Assert users item order (but each item is untouched because `@inherit`)
        $this->assertEquals([
            'Permissions' => 'http://localhost/cp/roles',
            'Groups' => 'http://localhost/cp/user-groups',
            'Users' => 'http://localhost/cp/users',
        ], $nav->get('Users')->mapWithKeys(fn ($i) => [$i->display() => $i->url()])->all());

        // Assert item modifications in renamed 'Site' section
        $this->assertEquals([
            'Collections' => 'http://localhost/cp/collections',
            'Navigation' => 'http://localhost/cp/navigation',
            'Categories' => 'http://localhost/cp/taxonomies',
            'Assets' => 'http://localhost/cp/assets',
            'Blueprints' => 'http://localhost/cp/fields/blueprints',
            'Flickr' => 'https://flickr.com',
            'Fieldsets' => 'http://localhost/cp/fields/fieldsets?modified',
        ], $nav->get('Site')->mapWithKeys(fn ($i) => [$i->display() => $i->url()])->all());

        // The `Fields` section was not explicitly defined in config, but `Blueprints` should be gone due to `@move`
        $this->assertEquals([
            'Fieldsets' => 'http://localhost/cp/fields/fieldsets',
        ], $nav->get('Fields')->mapWithKeys(fn ($i) => [$i->display() => $i->url()])->all());
    }

    #[Test]
    public function it_can_build_with_hidden_items()
    {
        $contentItems = $this->buildNavWithPreferences([
            'sections' => [
                'content' => [
                    'items' => [
                        'content::navigation' => '@hide',
                        'content::globals' => [
                            'action' => '@modify',
                            'display' => 'Globetrotters',
                        ],
                    ],
                ],
            ],
        ], true)->get('Content');

        $this->assertEquals(['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globetrotters'], $contentItems->map->display()->all());

        $this->assertEquals('@hide', $contentItems->keyBy->display()->get('Navigation')->manipulations()['action']);
        $this->assertEquals('@modify', $contentItems->keyBy->display()->get('Globetrotters')->manipulations()['action']);
    }

    #[Test]
    public function it_checks_active_status_on_moved_items()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('tags')->save();

        $items = $this->buildNavWithPreferences([
            'top_level' => [
                'content::collections::articles' => [
                    'action' => '@move',
                    'children' => [
                        'content::taxonomies::topics' => '@move',
                    ],
                ],
            ],
        ], true);

        $articles = $items->get('Top Level')->keyBy->display()->get('Articles');
        $topics = $articles->children()->first();
        $taxonomies = $items->get('Content')->keyBy->display()->get('Taxonomies');
        $tags = $items->get('Content')->keyBy->display()->get('Taxonomies')->children()->first();

        $this->assertFalse($articles->isActive());
        $this->assertFalse($topics->isActive());
        $this->assertFalse($taxonomies->isActive());
        $this->assertFalse($tags->isActive());

        Request::swap(Request::create('http://localhost/cp/collections/articles'));

        $this->assertTrue($articles->isActive());
        $this->assertFalse($topics->isActive());
        $this->assertFalse($taxonomies->isActive());
        $this->assertFalse($tags->isActive());

        Request::swap(Request::create('http://localhost/cp/taxonomies/topics'));

        $this->assertTrue($articles->isActive());
        $this->assertTrue($topics->isActive());
        $this->assertFalse($taxonomies->isActive());
        $this->assertFalse($tags->isActive());

        Request::swap(Request::create('http://localhost/cp/taxonomies/tags'));

        $this->assertFalse($articles->isActive());
        $this->assertFalse($topics->isActive());
        $this->assertTrue($taxonomies->isActive());
        $this->assertTrue($tags->isActive());
    }

    private function buildNavWithPreferences($preferences, $withHidden = false)
    {
        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());

        return Facades\CP\Nav::build($preferences, $withHidden)->pluck('items', 'display');
    }

    private function buildDefaultNav()
    {
        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());

        return Facades\CP\Nav::buildWithoutPreferences()->pluck('items', 'display');
    }
}
