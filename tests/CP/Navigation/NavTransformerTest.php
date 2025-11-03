<?php

namespace Tests\CP\Navigation;

use Facades\Statamic\CP\Navigation\NavItemIdHasher;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Navigation\NavTransformer;
use Statamic\Facades;
use Statamic\Support\Str;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NavTransformerTest extends TestCase
{
    use FakesRoles;
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

    private function transform($submission)
    {
        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());

        NavItemIdHasher::swap(new IncrementalIdHasher);

        return NavTransformer::fromVue($submission);
    }

    #[Test]
    public function it_transforms_no_manipulations_to_an_empty_array_to_allow_overriding_of_preferences_at_higher_levels()
    {
        $this->assertEquals([], $this->transform([]));
    }

    #[Test]
    public function it_can_create_new_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Top Level',
                'items' => [
                    [
                        'id' => 'custom_item',
                        'manipulations' => [
                            'action' => '@create',
                            'display' => 'Custom Item',
                            'url' => '/custom-item',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'top_level' => [
                'top_level::custom_item' => [
                    'action' => '@create',
                    'display' => 'Custom Item',
                    'url' => '/custom-item',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_create_new_item_children()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'custom_item',
                                'manipulations' => [
                                    'action' => '@create',
                                    'display' => 'Custom Item',
                                    'url' => '/custom-item',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::custom_item' => [
                            'action' => '@create',
                            'display' => 'Custom Item',
                            'url' => '/custom-item',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_move_an_item_to_another_section()
    {
        $transformed = $this->transform([
            [
                'display' => 'Top Level',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@move',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'top_level' => [
                'content::collections' => '@move',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_alias_item_to_another_section()
    {
        $transformed = $this->transform([
            [
                'display' => 'Top Level',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'top_level' => [
                'content::collections' => '@alias',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_alias_item_to_same_section()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => '@alias',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function aliasing_multiple_of_the_same_item_produces_unique_ids()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections:1' => '@alias',
                'content::collections:2' => '@alias',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_move_item_into_another_items_children()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'fields::blueprints',
                                'manipulations' => [
                                    'action' => '@move',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'fields::blueprints' => '@move',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_alias_item_into_another_items_children()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'fields::blueprints',
                                'manipulations' => [
                                    'action' => '@alias',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'fields::blueprints' => '@alias',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function aliasing_multiple_of_the_same_item_to_an_items_children_produces_unique_ids()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'fields::blueprints',
                                'manipulations' => [
                                    'action' => '@alias',
                                ],
                            ],
                            [
                                'id' => 'fields::blueprints',
                                'manipulations' => [
                                    'action' => '@alias',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'fields::blueprints:1' => '@alias',
                        'fields::blueprints:2' => '@alias',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_move_a_child_item_out_to_its_own_parent_item()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@move',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections::pages' => '@move',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_alias_a_child_item_out_to_its_own_parent_item()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections::pages' => '@alias',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function aliasing_multiple_of_the_same_child_item_produces_unique_ids()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections::pages:1' => '@alias',
                'content::collections::pages:2' => '@alias',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_modify_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                            'display' => 'Favourite Collections',
                        ],
                    ],
                    [
                        'id' => 'content::taxonomies',
                        'manipulations' => [
                            'action' => '@modify',
                            'url' => '/modified-taxonomies-url',
                        ],
                    ],
                    [
                        'id' => 'content::globals',
                        'manipulations' => [
                            'action' => '@modify',
                            'icon' => 'custom-svg',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'display' => 'Favourite Collections',
                ],
                'content::taxonomies' => [
                    'action' => '@modify',
                    'url' => '/modified-taxonomies-url',
                ],
                'content::globals' => [
                    'action' => '@modify',
                    'icon' => 'custom-svg',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_modify_item_children()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'content::collections::articles',
                                'manipulations' => [
                                    'action' => '@modify',
                                    'url' => '/modified-articles-url',
                                    'icon' => 'custom-svg', // This should get stripped out, because icons cannot be on children
                                ],
                            ],
                            [
                                'id' => 'content::collections::pages',
                                'manipulations' => [
                                    'action' => '@modify',
                                    'display' => 'Pagerinos',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => [
                            'action' => '@modify',
                            'display' => 'Pagerinos',
                        ],
                        'content::collections::articles' => [
                            'action' => '@modify',
                            'url' => '/modified-articles-url',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_modify_moved_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Top Level',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@move',
                            'display' => 'Favourite Collections',
                        ],
                    ],
                    [
                        'id' => 'content::taxonomies',
                        'manipulations' => [
                            'action' => '@move',
                            'url' => '/modified-taxonomies-url',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'top_level' => [
                'content::collections' => [
                    'action' => '@move',
                    'display' => 'Favourite Collections',
                ],
                'content::taxonomies' => [
                    'action' => '@move',
                    'url' => '/modified-taxonomies-url',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_modify_moved_children()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'fields::blueprints',
                                'manipulations' => [
                                    'action' => '@move',
                                    'display' => 'Blueprinterinos',
                                ],
                            ],
                            [
                                'id' => 'fields::fieldsets',
                                'manipulations' => [
                                    'action' => '@move',
                                    'url' => '/modified-fieldsets-url',
                                    'icon' => 'custom-svg', // This should get stripped out, because icons cannot be on children
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'fields::blueprints' => [
                            'action' => '@move',
                            'display' => 'Blueprinterinos',
                        ],
                        'fields::fieldsets' => [
                            'action' => '@move',
                            'url' => '/modified-fieldsets-url',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_modify_aliased_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Top Level',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                            'display' => 'Favourite Collections',
                        ],
                    ],
                    [
                        'id' => 'content::taxonomies',
                        'manipulations' => [
                            'action' => '@alias',
                            'url' => '/modified-taxonomies-url',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'top_level' => [
                'content::collections' => [
                    'action' => '@alias',
                    'display' => 'Favourite Collections',
                ],
                'content::taxonomies' => [
                    'action' => '@alias',
                    'url' => '/modified-taxonomies-url',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_modify_aliased_children()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'fields::blueprints',
                                'manipulations' => [
                                    'action' => '@alias',
                                    'display' => 'Blueprinterinos',
                                ],
                            ],
                            [
                                'id' => 'fields::fieldsets',
                                'manipulations' => [
                                    'action' => '@alias',
                                    'url' => '/modified-fieldsets-url',
                                    'icon' => 'custom-svg', // This should get stripped out, because icons cannot be on children
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'fields::blueprints' => [
                            'action' => '@alias',
                            'display' => 'Blueprinterinos',
                        ],
                        'fields::fieldsets' => [
                            'action' => '@alias',
                            'url' => '/modified-fieldsets-url',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_hide_an_item()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@hide',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => '@hide',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_hide_a_child_item()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'content::collections::pages',
                                'manipulations' => [
                                    'action' => '@hide',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => '@hide',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_reorder_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    ['id' => 'content::navigation'],
                    ['id' => 'content::taxonomies'],
                    ['id' => 'content::assets'],
                    ['id' => 'content::collections'],
                    ['id' => 'content::globals'],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'reorder' => [
                    'content::navigation',
                    'content::taxonomies',
                    'content::assets',
                    // 'Collections' and 'Globals' items are omitted because they are redundant in this case
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_reorder_custom_and_modified_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    ['id' => 'content::collections'],
                    ['id' => 'content::navigation'],
                    [
                        'id' => 'content::custom_item_one',
                        'manipulations' => [
                            'action' => '@create',
                            'display' => 'Custom Item One',
                        ],
                    ],
                    [
                        'id' => 'content::taxonomies',
                        'manipulations' => [
                            'action' => '@modify',
                            'display' => 'Favourite Taxonomies',
                        ],
                    ],
                    ['id' => 'content::assets'],
                    ['id' => 'content::globals'],
                    [
                        'id' => 'content::custom_item_two',
                        'manipulations' => [
                            'action' => '@create',
                            'display' => 'Custom Item Two',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'reorder' => [
                    'content::collections',
                    'content::navigation',
                    'content::custom_item_one',
                    // The rest should omitted because they're left in same order at the end of the list, therefore redundant
                ],
                'items' => [
                    'content::taxonomies' => [
                        'action' => '@modify',
                        'display' => 'Favourite Taxonomies',
                    ],
                    'content::custom_item_one' => [
                        'action' => '@create',
                        'display' => 'Custom Item One',
                    ],
                    'content::custom_item_two' => [
                        'action' => '@create',
                        'display' => 'Custom Item Two',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_reorder_child_items()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            ['id' => 'content::collections::new_item', 'manipulations' => ['action' => '@create', 'display' => 'New Item']],
                            ['id' => 'content::collections::pages'],
                            ['id' => 'content::collections::articles'],
                            ['id' => 'content::taxonomies::topics', 'manipulations' => ['action' => '@move']],
                            ['id' => 'content::collections::new_item_at_end', 'manipulations' => ['action' => '@create', 'display' => 'New Item At End']],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'reorder' => [
                        'content::collections::new_item',
                        'content::collections::pages',
                    ],
                    'children' => [
                        'content::collections::new_item' => [
                            'action' => '@create',
                            'display' => 'New Item',
                        ],
                        'content::taxonomies::topics' => '@move',
                        'content::collections::new_item_at_end' => [
                            'action' => '@create',
                            'display' => 'New Item At End',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_create_a_new_section()
    {
        $transformed = $this->transform([
            [
                'display' => 'Custom Section',
                'action' => '@create',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'custom_section' => [
                'display' => 'Custom Section',
                'action' => '@create',
                'items' => [
                    'content::collections::pages' => '@alias',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_create_a_new_section_with_special_characters_in_display()
    {
        $transformed = $this->transform([
            [
                'display' => 'Foo & Bar Section (One + Two)',
                'action' => '@create',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'foo_bar_section_one_two' => [
                'display' => 'Foo & Bar Section (One + Two)',
                'action' => '@create',
                'items' => [
                    'content::collections::pages' => '@alias',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_ignores_new_section_which_contain_no_manipulations()
    {
        $transformed = $this->transform([
            [
                'display_original' => 'Custom Section',
                'items' => [],
            ],
        ]);

        $this->assertEquals([], $transformed);
    }

    #[Test]
    public function it_can_rename_a_section()
    {
        $transformed = $this->transform([
            [
                'display_original' => 'Content',
                'display' => 'Favourite Content',
            ],
        ]);

        $expected = [
            'content' => [
                'display' => 'Favourite Content',
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_hide_a_section()
    {
        $transformed = $this->transform([
            [
                'display_original' => 'Content',
                'action' => '@hide',
            ],
        ]);

        $expected = [
            'content' => '@hide',
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_hide_a_section_containing_item_manipulations()
    {
        $transformed = $this->transform([
            [
                'display_original' => 'Content',
                'action' => '@hide',
                'items' => [
                    [
                        'id' => 'fields::blueprints',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'action' => '@hide',
                'items' => [
                    'fields::blueprints' => '@alias',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_reorder_sections()
    {
        $transformed = $this->transform([
            ['display_original' => 'Top Level'],
            ['display_original' => 'Fields'],
            ['display_original' => 'Tools'],
            ['display_original' => 'Content'],
            ['display_original' => 'Settings'],
            ['display_original' => 'Users'],
        ]);

        $expected = [
            'reorder' => [
                // 'Top Level' is omitted because it'll always be top level
                'fields',
                'tools',
                // 'Content', 'Settings', and 'Users' sections are omitted because they are redundant in this case
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_reorder_custom_and_modified_sections()
    {
        $transformed = $this->transform([
            ['display_original' => 'Top Level'],
            ['display_original' => 'Content'],
            [
                'display' => 'Fields Customized',
                'display_original' => 'Fields',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
            [
                'display' => 'Custom Section',
                'action' => '@create',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
            ['display_original' => 'Tools'],
            ['display_original' => 'Settings'],
            ['display_original' => 'Users'],
            [
                'display' => 'Custom Section At End',
                'action' => '@create',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'reorder' => [
                'content',
                'fields',
                'custom_section',
                // The rest should omitted because they're left in same order at the end of the list, therefore redundant
            ],
            'sections' => [
                'fields' => [
                    'display' => 'Fields Customized',
                    'items' => [
                        'content::collections' => '@alias',
                    ],
                ],
                'custom_section' => [
                    'display' => 'Custom Section',
                    'action' => '@create',
                    'items' => [
                        'content::collections::pages' => '@alias',
                    ],
                ],
                'custom_section_at_end' => [
                    'display' => 'Custom Section At End',
                    'action' => '@create',
                    'items' => [
                        'content::collections::pages' => '@alias',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_reorder_sections_but_ignores_custom_sections_left_at_the_end()
    {
        $transformed = $this->transform([
            ['display_original' => 'Top Level'],
            [
                'display_original' => 'Fields',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
            ['display_original' => 'Tools'],
            ['display_original' => 'Content'],
            ['display_original' => 'Users'],
            ['display_original' => 'Settings'],
            [
                'display' => 'Custom Section',
                'action' => '@create',
                'items' => [
                    [
                        'id' => 'content::collections::pages',
                        'manipulations' => [
                            'action' => '@alias',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'reorder' => [
                'fields',
                'tools',
                'content',
                'users',
                // `Settings` and `Custom Section` are omitted because they are left over items in the same order they originally were, therefore redundant
                // Also `Custom Section` is omitted because it's a new item at the end of the list, so it doesn't need to be in the order
            ],
            'sections' => [
                'fields' => [
                    'content::collections' => '@alias',
                ],
                'custom_section' => [
                    'display' => 'Custom Section',
                    'action' => '@create',
                    'items' => [
                        'content::collections::pages' => '@alias',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_ignores_items_with_no_manipulations()
    {
        $transformed = $this->transform([
            [
                'display' => 'Top Level',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [], // This item should be ignored
                    ],
                ],
            ],
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                        ],
                        'children' => [
                            [
                                'id' => 'content::collections::articles',
                                'manipulations' => [], // This item should be ignored
                            ],
                            [
                                'id' => 'content::collections::pages', // This is the only item we're actually modifying
                                'manipulations' => [
                                    'action' => '@modify',
                                    'display' => 'Pagerinos',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'content::collections::pages' => [
                            'action' => '@modify',
                            'display' => 'Pagerinos',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_add_unique_hash_to_an_id()
    {
        $id = NavTransformer::uniqueId('test::id');

        $this->assertTrue((bool) Str::startsWith($id, 'test::id:'));
        $this->assertTrue((bool) preg_match('/.*[^\:]:[^\:]{6}$/', $id));
    }

    #[Test]
    public function it_can_remove_unique_hash_from_an_id()
    {
        $this->assertEquals('test::id', NavTransformer::removeUniqueIdHash('test::id:587bac'));
    }

    #[Test]
    public function it_intelligently_handles_url_modifications()
    {
        $transformed = $this->transform([
            [
                'display' => 'Content',
                'items' => [
                    [
                        'id' => 'content::collections',
                        'manipulations' => [
                            'action' => '@modify',
                            'url' => '/absolute-url',
                        ],
                    ],
                    [
                        'id' => 'content::taxonomies',
                        'manipulations' => [
                            'action' => '@modify',
                            'url' => 'relative-cp-url',
                        ],
                    ],
                    [
                        'id' => 'content::assets',
                        'manipulations' => [
                            'action' => '@modify',
                            'url' => 'http://localhost/cp/assets/custom-pasted-cp-url',
                        ],
                    ],
                    [
                        'id' => 'content::globals',
                        'manipulations' => [
                            'action' => '@modify',
                            'url' => 'https://external-url.com',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'url' => '/absolute-url',
                ],
                'content::taxonomies' => [
                    'action' => '@modify',
                    'url' => 'relative-cp-url',
                ],
                'content::assets' => [
                    'action' => '@modify',
                    'url' => 'assets/custom-pasted-cp-url',
                ],
                'content::globals' => [
                    'action' => '@modify',
                    'url' => 'https://external-url.com',
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }

    #[Test]
    public function it_can_transform_complex_json_payload_copied_from_actual_vue_submission()
    {
        $transformed = $this->transform(json_decode('[{"display":"Top Level","display_original":"Top Level","action":false,"items":[{"id":"top_level::dashboard","manipulations":[],"children":[]},{"id":"content::collections::pages","manipulations":{"action":"@alias"},"children":[]},{"id":"tools::updates","manipulations":{"action":"@move"},"children":[]},{"id":"new_top_level_item","manipulations":{"action":"@create","display":"New Top Level Item","url":"\/new-top-level-item","icon":null},"children":[{"id":"new_child_item","manipulations":{"action":"@create","display":"New Child Item","url":"\/new-child-item","icon":null},"children":[]}]}]},{"display":"Content","display_original":"Content","action":false,"items":[{"id":"content::collections","manipulations":{"action":"@modify","reorder":["content::collections::new_item","content::collections::pages"]},"children":[{"id":"content::collections::new_item","manipulations":{"action":"@create","display":"New Item","url":"\/new"},"children":[]},{"id":"content::collections::pages","manipulations":[],"children":[]},{"id":"content::collections::articles","manipulations":[],"children":[]},{"id":"content::taxonomies::topics","manipulations":{"action":"@move"},"children":[]},{"id":"content::collections::new_item_at_end","manipulations":{"action":"@create","display":"New Item At End","url":"\/new"},"children":[]}]},{"id":"content::navigation","manipulations":{"action":"@hide"},"children":[]},{"id":"content::new_item","manipulations":{"action":"@create","display":"New Item","url":"\/new"},"children":[]},{"id":"content::taxonomies","manipulations":[],"children":[]},{"id":"content::assets","manipulations":[],"children":[{"id":"content::assets::assets","manipulations":[],"children":[]}]},{"id":"content::globals","manipulations":[],"children":[{"id":"content::globals::pricing","manipulations":[],"children":[]},{"id":"content::globals::settings","manipulations":[],"children":[]}]},{"id":"content::new_item_at_end","manipulations":{"action":"@create","display":"New Item At End","url":"\/end"},"children":[]}]},{"display":"Fieldsss","display_original":"Fields","action":false,"items":[{"id":"fields::fieldsets","manipulations":[],"children":[]},{"id":"fields::blueprints","manipulations":[],"children":[]}]},{"display":"Custom Section","display_original":"My Section","action":"@create","items":[{"id":"my_section::my_item","manipulations":{"action":"@create","display":"Custom item","url":"\/url","icon":null},"children":[]}]},{"display":"Tools","display_original":"Tools","action":false,"items":[{"id":"tools::forms","manipulations":[],"children":[]},{"id":"tools::addons","manipulations":[],"children":[]},{"id":"tools::utilities","manipulations":[],"children":[{"id":"tools::utilities::cache","manipulations":[],"children":[]},{"id":"tools::utilities::email","manipulations":[],"children":[]},{"id":"tools::utilities::licensing","manipulations":[],"children":[]},{"id":"tools::utilities::php_info","manipulations":[],"children":[]},{"id":"tools::utilities::search","manipulations":[],"children":[]}]}]},{"display":"Settings","display_original":"Settings","action":false,"items":[{"id":"settings::site","manipulations":[],"children":[]},{"id":"settings::preferences","manipulations":[],"children":[{"id":"settings::preferences::general","manipulations":[],"children":[]},{"id":"settings::preferences::cp_nav","manipulations":[],"children":[]}]}]},{"display":"Users","display_original":"Users","action":false,"items":[{"id":"users::users","manipulations":[],"children":[]},{"id":"users::groups","manipulations":[],"children":[]}]},{"display":"Custom Section At End","display_original":"Another section","action":"@create","items":[{"id":"users::permissions","manipulations":{"action":"@move"},"children":[{"id":"users::permissions::author","manipulations":[],"children":[]}]}]}]', true));

        $expected = [
            'reorder' => [
                'content',
                'fields',
                'custom_section',
            ],
            'sections' => [
                'top_level' => [
                    'content::collections::pages' => '@alias',
                    'tools::updates' => '@move',
                    'top_level::new_top_level_item' => [
                        'action' => '@create',
                        'display' => 'New Top Level Item',
                        'url' => '/new-top-level-item',
                        'children' => [
                            'top_level::new_top_level_item::new_child_item' => [
                                'action' => '@create',
                                'display' => 'New Child Item',
                                'url' => '/new-child-item',
                            ],
                        ],
                    ],
                ],
                'content' => [
                    'reorder' => [
                        'content::collections',
                        'content::navigation',
                        'content::new_item',
                    ],
                    'items' => [
                        'content::collections' => [
                            'action' => '@modify',
                            'reorder' => [
                                'content::collections::new_item',
                                'content::collections::pages',
                            ],
                            'children' => [
                                'content::collections::new_item' => [
                                    'action' => '@create',
                                    'display' => 'New Item',
                                    'url' => '/new',
                                ],
                                'content::taxonomies::topics' => '@move',
                                'content::collections::new_item_at_end' => [
                                    'action' => '@create',
                                    'display' => 'New Item At End',
                                    'url' => '/new',
                                ],
                            ],
                        ],
                        'content::navigation' => '@hide',
                        'content::new_item' => [
                            'action' => '@create',
                            'display' => 'New Item',
                            'url' => '/new',
                        ],
                        'content::new_item_at_end' => [
                            'action' => '@create',
                            'display' => 'New Item At End',
                            'url' => '/end',
                        ],
                    ],
                ],
                'fields' => [
                    'display' => 'Fieldsss',
                    'reorder' => [
                        'fields::fieldsets',
                    ],
                ],
                'custom_section' => [
                    'action' => '@create',
                    'display' => 'Custom Section',
                    'items' => [
                        'custom_section::custom_item' => [
                            'action' => '@create',
                            'display' => 'Custom item',
                            'url' => '/url',
                        ],
                    ],
                ],
                'custom_section_at_end' => [
                    'action' => '@create',
                    'display' => 'Custom Section At End',
                    'items' => [
                        'users::permissions' => '@move',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $transformed);
    }
}

class IncrementalIdHasher
{
    protected $count = 1;

    public function appendHash($id)
    {
        $id = $id.':'.$this->count;

        $this->count++;

        return $id;
    }
}
