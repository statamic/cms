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
    public function it_can_create_new_items_on_the_fly()
    {
        // It can create item...
        $item = $this->buildNavWithPreferences([
            'top_level' => [
                'favs' => [
                    'action' => '@create',
                    'display' => 'Favourites',
                    'url' => 'https://pinterest.com',
                    'icon' => '<svg>custom</svg>',
                    'children' => [
                        'One' => '/one',
                        'Two' => '/two',
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

    /** @test */
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
        $this->assertEquals('fields::blueprints::clone', $item->id());
        $this->assertEquals('Redprints', $item->display());
        $this->assertEquals('https://redprints.com', $item->url());
        $this->assertEquals('<svg>custom</svg>', $item->icon());
        $this->assertEquals(['fields::blueprints::clone::one', 'fields::blueprints::clone::two'], $item->children()->map->id()->all());
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
        $this->assertEquals('fields::blueprints::clone', $item->id());
        $this->assertEquals('Redprints', $item->display());
        $this->assertEquals('https://redprints.com', $item->url());
        $this->assertEquals('<svg>custom</svg>', $item->icon());
        $this->assertEquals(['fields::blueprints::clone::one', 'fields::blueprints::clone::two'], $item->children()->map->id()->all());
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

    /** @test */
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
        $this->assertEquals('fields::blueprints::clone', $nav->get('Top Level')->keyBy->display()->get('Redprints')->id());
        $this->assertEquals('Redprints', $nav->get('Top Level')->keyBy->display()->get('Redprints')->display());
        $this->assertEquals('https://redprints.com', $nav->get('Top Level')->keyBy->display()->get('Redprints')->url());

        // Assert the original item...
        $this->assertEquals('fields::blueprints', $nav->get('Fields')->keyBy->display()->get('Blueprints')->id());
        $this->assertEquals('Blueprints', $nav->get('Fields')->keyBy->display()->get('Blueprints')->display());
        $this->assertEquals('http://localhost/cp/fields/blueprints', $nav->get('Fields')->keyBy->display()->get('Blueprints')->url());
    }

    /** @test */
    public function it_can_set_item_children_using_same_modify_setters()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_can_alias_an_item_into_the_children_of_another_item()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_can_move_an_item_into_the_children_of_another_item()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_can_alias_a_newly_created_item_to_an_earlier_section()
    {
        $nav = $this->buildNavWithPreferences([
            'top_level' => [
                'tools::technologies::json' => '@alias',
            ],
            'tools' => [
                'techs' => [
                    'action' => '@create',
                    'display' => 'Technologies',
                    'children' => [
                        'Json' => 'https://json.org',
                        'Yaml' => 'https://yaml.org',
                    ],
                ],
            ],
        ]);

        $jsonItem = $nav->get('Tools')->keyBy->display()->get('Technologies')->children()->first();
        $this->assertEquals('tools::technologies::json', $jsonItem->id());
        $this->assertEquals('Json', $jsonItem->display());
        $this->assertEquals('https://json.org', $jsonItem->url());

        $yamlItem = $nav->get('Tools')->keyBy->display()->get('Technologies')->children()->last();
        $this->assertEquals('tools::technologies::yaml', $yamlItem->id());
        $this->assertEquals('Yaml', $yamlItem->display());
        $this->assertEquals('https://yaml.org', $yamlItem->url());

        $aliasedJsonItem = $nav->get('Top Level')->keyBy->display()->get('Json');
        $this->assertEquals('tools::technologies::json::clone', $aliasedJsonItem->id());
        $this->assertEquals('Json', $aliasedJsonItem->display());
        $this->assertEquals('https://json.org', $aliasedJsonItem->url());
    }

    /** @test */
    public function it_respects_order_that_items_are_aliased_and_created()
    {
        $items = $this->buildNavWithPreferences([
            'top_level' => [
                'fields::blueprints' => '@move',
                'fields::fieldsets' => '@alias',
                'tools::technologies' => [
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
