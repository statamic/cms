<?php

namespace Tests\CP\Navigation;

use Illuminate\Support\Arr;
use Statamic\CP\Navigation\NavPreferencesNormalizer;
use Tests\TestCase;

class NavPreferencesNormalizerTest extends TestCase
{
    use Concerns\HashedIdAssertions;

    private function normalize($config)
    {
        return NavPreferencesNormalizer::fromPreferences($config);
    }

    /** @test */
    public function it_ensures_normalization_at_top_level()
    {
        $nav = $this->normalize([
            'content' => [
                'fields::blueprints' => '@alias',
            ],
        ]);

        $this->assertFalse(Arr::has($nav, 'content'));
        $this->assertFalse(Arr::get($nav, 'reorder'));
        $this->assertTrue(Arr::has($nav, 'sections.content'));
    }

    /** @test */
    public function it_ensures_normalization_of_section()
    {
        $nav = $this->normalize([
            'content' => [
                'fields::blueprints' => '@alias',
            ],
        ]);

        $this->assertFalse(Arr::get($nav, 'sections.content.reorder'));
        $this->assertFalse(Arr::get($nav, 'sections.content.display'));
        $this->assertHasHashedIdFor('fields::blueprints', Arr::get($nav, 'sections.content.items'));
    }

    /** @test */
    public function it_ensures_normalization_of_item()
    {
        $nav = $this->normalize([
            'content' => [
                'fields::blueprints' => '@alias', // action as string
                'user::profiles' => [
                    'action' => '@move', // action in array config
                ],
                'tools::utilities::php_info' => [], // inferred action
            ],
        ]);

        $blueprintsId = $this->assertHasHashedIdFor('fields::blueprints', Arr::get($nav, 'sections.content.items'));
        $phpInfoId = $this->assertHasHashedIdFor('tools::utilities::php_info', Arr::get($nav, 'sections.content.items'));

        $expected = [
            $blueprintsId => [
                'action' => '@alias',
            ],
            'user::profiles' => [
                'action' => '@move',
            ],
            $phpInfoId => [
                'action' => '@alias',
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.content.items'));
    }

    /** @test */
    public function it_ensures_normalization_of_children()
    {
        $nav = $this->normalize([
            'content' => [
                'content::collections' => [
                    'action' => '@modify',
                    'children' => [
                        'Json' => 'https://jsonvarga.net', // inferred action
                        'fields::blueprints' => '@alias', // action as string
                        'user::profiles' => [
                            'action' => '@move', // action in array config
                        ],
                        'fields::fieldsets' => [], // inferred action
                    ],
                ],
            ],
        ]);

        $blueprintsId = $this->assertHasHashedIdFor('fields::blueprints', Arr::get($nav, 'sections.content.items.content::collections.children'));
        $fieldsetsId = $this->assertHasHashedIdFor('fields::fieldsets', Arr::get($nav, 'sections.content.items.content::collections.children'));

        $expected = [
            'Json' => [
                'action' => '@create',
                'display' => 'Json',
                'url' => 'https://jsonvarga.net',
            ],
            $blueprintsId => [
                'action' => '@alias',
            ],
            'user::profiles' => [
                'action' => '@move',
            ],
            $fieldsetsId => [
                'action' => '@alias',
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.content.items.content::collections.children'));
    }

    /** @test */
    public function it_ensures_top_level_section_is_always_first_returned_section()
    {
        // Minimal sections config
        $this->assertEquals(['top_level', 'content'], array_keys($this->normalize([
            'content' => ['fields::blueprints' => '@alias'],
            'top_level' => ['content::collections::pages' => '@alias'],
        ])['sections']));

        // With `reorder: true`
        $this->assertEquals(['top_level', 'content'], array_keys($this->normalize([
            'reorder' => true,
            'content' => ['fields::blueprints' => '@alias'],
            'top_level' => ['content::collections::pages' => '@alias'],
        ])['sections']));

        // With `reorder: true` and sections properly nested
        $this->assertEquals(['top_level', 'content'], array_keys($this->normalize([
            'reorder' => true,
            'sections' => [
                'content' => ['fields::blueprints' => '@alias'],
                'top_level' => ['content::collections::pages' => '@alias'],
            ],
        ])['sections']));
    }

    /** @test */
    public function it_returns_section_display_when_renaming()
    {
        $nav = $this->normalize([
            'content' => [
                'display' => 'Favourite Content!',
            ],
        ]);

        $this->assertEquals('Favourite Content!', Arr::get($nav, 'sections.content.display'));
    }

    /** @test */
    public function it_returns_section_action_when_removing()
    {
        $nav = $this->normalize([
            'content' => [
                'action' => '@hide',
            ],
            'fields' => [
                'display' => 'Favourite Fields!',
            ],
        ]);

        $this->assertEquals('@hide', Arr::get($nav, 'sections.content.action'));
        $this->assertFalse(Arr::get($nav, 'sections.fields.action'));
    }

    /** @test */
    public function it_removes_inherit_action_sections_when_not_reordering()
    {
        $this->assertEquals(['users'], array_keys($this->normalize([
            'top_level' => '@inherit',
            'collections' => '@inherit',
            'fields' => '@inherit',
            'users' => [
                'content::collections::profiles' => '@move',
            ],
            'tools' => '@inherit',
        ])['sections']));
    }

    /** @test */
    public function it_doesnt_remove_inherit_action_sections_when_actually_reordering()
    {
        // With `reorder: true`
        $this->assertEquals(['top_level', 'users', 'tools'], array_keys($this->normalize([
            'reorder' => true,
            'top_level' => '@inherit',
            'users' => [
                'content::collections::profiles' => '@move',
            ],
            'tools' => '@inherit',
        ])['sections']));

        // With `reorder: true` and sections properly nested
        $this->assertEquals(['top_level', 'users', 'tools'], array_keys($this->normalize([
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'users' => [
                    'content::collections::profiles' => '@move',
                ],
                'tools' => '@inherit',
            ],
        ])['sections']));
    }

    /** @test */
    public function it_removes_inherit_action_items_when_not_reordering()
    {
        $this->assertEquals(['content::collections::posts'], array_keys($this->normalize([
            'content' => [
                'content::collections::pages' => '@inherit',
                'content::collections::posts' => ['display' => 'Posterinos'],
                'content::collections::profiles' => '@inherit',
            ],
        ])['sections']['content']['items']));
    }

    /** @test */
    public function it_doesnt_remove_inherit_action_items_when_actually_reordering()
    {
        $expected = [
            'content::collections::pages',
            'content::collections::posts',
            'content::collections::profiles',
        ];

        // With `reorder: true`
        $this->assertEquals($expected, array_keys($this->normalize([
            'content' => [
                'reorder' => true,
                'content::collections::pages' => '@inherit',
                'content::collections::posts' => ['display' => 'Posterinos'],
                'content::collections::profiles' => '@inherit',
            ],
        ])['sections']['content']['items']));

        // With `reorder: true` and sections properly nested
        $this->assertEquals($expected, array_keys($this->normalize([
            'content' => [
                'reorder' => true,
                'items' => [
                    'content::collections::pages' => '@inherit',
                    'content::collections::posts' => ['display' => 'Posterinos'],
                    'content::collections::profiles' => '@inherit',
                ],
            ],
        ])['sections']['content']['items']));
    }

    /**
     * @test
     *
     * @dataProvider modifiersProvider
     **/
    public function it_defaults_action_to_modify_when_modifying_in_original_section($modifier)
    {
        // With `reorder: true`
        $this->assertEquals('@modify', Arr::get($this->normalize([
            'content' => [
                'reorder' => true,
                'content::collections::pages' => [
                    $modifier => 'test',
                ],
            ],
        ]), 'sections.content.items.content::collections::pages.action'));

        // With `reorder: true` and sections properly nested
        $this->assertEquals('@modify', Arr::get($this->normalize([
            'content' => [
                'reorder' => true,
                'items' => [
                    'content::collections::pages' => [
                        $modifier => 'test',
                    ],
                ],
            ],
        ]), 'sections.content.items.content::collections::pages.action'));
    }

    public static function modifiersProvider()
    {
        return collect(NavPreferencesNormalizer::ALLOWED_NAV_ITEM_MODIFICATIONS)->map(fn ($key) => [$key]);
    }

    /** @test */
    public function it_defaults_action_to_inherit_when_reordering_in_original_section()
    {
        // With `reorder: true`
        $this->assertEquals('@inherit', Arr::get($this->normalize([
            'content' => [
                'reorder' => true,
                'content::collections::pages' => [],
            ],
        ]), 'sections.content.items.content::collections::pages.action'));

        // With `reorder: true` and sections properly nested
        $this->assertEquals('@inherit', Arr::get($this->normalize([
            'content' => [
                'reorder' => true,
                'items' => [
                    'content::collections::pages' => [],
                ],
            ],
        ]), 'sections.content.items.content::collections::pages.action'));
    }

    /** @test */
    public function it_defaults_action_to_alias_when_in_another_section()
    {
        $nav = $this->normalize([
            'top_level' => [
                'content::collections::pages' => [],
            ],
        ]);

        $pagesId = $this->assertHasHashedIdFor('content::collections::pages', Arr::get($nav, 'sections.top_level.items'));

        $this->assertEquals('@alias', Arr::get($nav, "sections.top_level.items.{$pagesId}.action"));

        $nav = $this->normalize([
            'top_level' => [
                'content::collections::pages' => [
                    'display' => 'Pagerinos',
                    'url' => '/pagerinos',
                ],
            ],
        ]);

        $pagesId = $this->assertHasHashedIdFor('content::collections::pages', Arr::get($nav, 'sections.top_level.items'));

        $this->assertEquals('@alias', Arr::get($nav, "sections.top_level.items.{$pagesId}.action"));
    }

    /** @test */
    public function it_allows_creating_of_items_on_the_fly_using_create_action()
    {
        $nav = $this->normalize([
            'content' => [
                'user::profiles' => [
                    'action' => '@create', // The `@create` action is required to use the following setters...
                    'display' => 'Profiles',
                    'url' => '/profiles',
                    'icon' => 'user',
                    'children' => [
                        'Json' => 'https://jsonvarga.net',
                        'spaml' => [
                            'action' => '@create',
                            'display' => 'Yaml',
                            'url' => 'https://spamlyaml.org',
                        ],
                    ],
                    'invalid_nav_item_setter' => 'test', // This should get removed as it's not a valid setter.
                ],
            ],
        ]);

        $expected = [
            'action' => '@create',
            'display' => 'Profiles',
            'url' => '/profiles',
            'icon' => 'user',
            'children' => [
                'Json' => [
                    'action' => '@create',
                    'display' => 'Json',
                    'url' => 'https://jsonvarga.net',
                ],
                'spaml' => [
                    'action' => '@create',
                    'display' => 'Yaml',
                    'url' => 'https://spamlyaml.org',
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.content.items.user::profiles'));
    }

    /** @test */
    public function it_allows_modifying_of_items_using_modify_action()
    {
        $nav = $this->normalize([
            'top_level' => [
                'top_level::dashboard' => [
                    'action' => '@modify', // The `@modify` action is required to use the following setters on the original nav item...
                    'display' => 'Dashboard Confessional',
                    'url' => '/dashboard-confessional',
                    'icon' => 'music',
                    'children' => [
                        'Statamic Dashboard' => '/dashboard', // This should get normalized as well
                    ],
                    'invalid_nav_item_setter' => 'test', // This should get removed as it's not a valid setter.
                ],
            ],
        ]);

        $expected = [
            'action' => '@modify',
            'display' => 'Dashboard Confessional',
            'url' => '/dashboard-confessional',
            'icon' => 'music',
            'children' => [
                'Statamic Dashboard' => [
                    'action' => '@create',
                    'display' => 'Statamic Dashboard',
                    'url' => '/dashboard',
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.top_level.items.top_level::dashboard'));
    }

    /** @test */
    public function it_allows_modifying_of_child_items_using_modify_action()
    {
        $nav = $this->normalize([
            'content' => [
                'content::collections' => [
                    'action' => '@modify', // The `@modify` action is required to use the following setters on the original nav item...
                    'children' => [
                        'content::collections::pages' => [
                            'action' => '@modify', // The `@modify` action is required to use the following setters on the original nav item...
                            'display' => 'Pagerinos',
                            'url' => '/pagerinos',
                            'icon' => 'music', // This doesn't matter for the child itself, but it can matter when aliasing from a child to a top level item
                            'children' => [], // This should get removed as children can't have children
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'content::collections' => [
                'action' => '@modify',
                'children' => [
                    'content::collections::pages' => [
                        'action' => '@modify',
                        'display' => 'Pagerinos',
                        'url' => '/pagerinos',
                        'icon' => 'music',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.content.items'));
    }

    /** @test */
    public function it_removes_section_specific_actions_that_might_be_confusing_to_js_nav_builder()
    {
        $nav = $this->normalize([
            'top_level' => [
                'reorder' => true,
                'items' => [
                    'top_level::create' => '@create',
                    'top_level::hide' => '@hide',
                    'top_level::modify' => '@modify',
                    'top_level::alias' => '@alias',
                    'top_level::inherit' => '@inherit',
                    'top_level::move' => '@move', // if reordering use `@inherit`, or if modifying use `@modify`
                ],
            ],
            'content' => [
                'reorder' => true,
                'items' => [
                    'top_level::create' => '@create',
                    'top_level::hide' => '@hide', // if hiding, put item/action in it's proper section
                    'top_level::modify' => '@modify', // if you're moving or aliasing into this section, modifying will work with those actions
                    'top_level::alias' => '@alias',
                    'top_level::inherit' => '@inherit', // if you're moving or aliasing into this section, reordering will work with those actions
                    'top_level::move' => '@move',
                ],
            ],
        ]);

        $topLevelAliasId = $this->assertHasHashedIdFor('top_level::alias', Arr::get($nav, 'sections.top_level.items'));
        $contentAliasId = $this->assertHasHashedIdFor('top_level::alias', Arr::get($nav, 'sections.content.items'));

        $expectedTopLevelItems = [
            'top_level::create',
            'top_level::hide',
            'top_level::modify',
            $topLevelAliasId,
            'top_level::inherit',
            'top_level::move',
        ];

        $expectedContentItems = [
            'top_level::create',
            $contentAliasId,
            'top_level::move',
        ];

        $this->assertEquals($expectedTopLevelItems, array_keys(Arr::get($nav, 'sections.top_level.items')));
        $this->assertEquals($expectedContentItems, array_keys(Arr::get($nav, 'sections.content.items')));
    }

    /** @test */
    public function it_normalizes_an_example_config()
    {
        $nav = $this->normalize([
            'top_level' => [
                'top_level::dashboard' => [
                    'display' => 'Dashboard Confessional',
                ],
                'fields::blueprints' => '@alias',
                'content::collections::pages' => '@move',
            ],
            'content' => [
                'fields::blueprints' => [
                    'display' => 'Content Blueprints',
                ],
                'user::profiles' => [
                    'action' => '@create',
                    'url' => '/profiles',
                    'icon' => 'user',
                ],
            ],
            'fields' => [
                'action' => '@hide',
            ],
        ]);

        $topLevelBlueprintsId = $this->assertHasHashedIdFor('fields::blueprints', Arr::get($nav, 'sections.top_level.items'));
        $contentBlueprintsId = $this->assertHasHashedIdFor('fields::blueprints', Arr::get($nav, 'sections.content.items'));

        $expected = [
            'reorder' => false,
            'sections' => [
                'top_level' => [
                    'action' => false,
                    'reorder' => false,
                    'display' => false,
                    'items' => [
                        'top_level::dashboard' => [
                            'action' => '@modify',
                            'display' => 'Dashboard Confessional',
                        ],
                        $topLevelBlueprintsId => [
                            'action' => '@alias',
                        ],
                        'content::collections::pages' => [
                            'action' => '@move',
                        ],
                    ],
                ],
                'content' => [
                    'action' => false,
                    'reorder' => false,
                    'display' => false,
                    'items' => [
                        $contentBlueprintsId => [
                            'action' => '@alias',
                            'display' => 'Content Blueprints',
                        ],
                        'user::profiles' => [
                            'action' => '@create',
                            'url' => '/profiles',
                            'icon' => 'user',
                        ],
                    ],
                ],
                'fields' => [
                    'action' => '@hide',
                    'reorder' => false,
                    'display' => false,
                    'items' => [],
                ],
            ],
        ];

        $this->assertEquals($expected, $nav);
    }
}
