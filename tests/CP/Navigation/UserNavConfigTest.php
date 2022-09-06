<?php

namespace Tests\CP\Navigation;

use Illuminate\Support\Arr;
use Statamic\CP\Navigation\UserNavConfig;
use Tests\TestCase;

class UserNavConfigTest extends TestCase
{
    private function normalize($config)
    {
        return UserNavConfig::normalize($config)->get();
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
        $this->assertEquals('Content', Arr::get($nav, 'sections.content.display'));
        $this->assertNull(Arr::get($nav, 'sections.content.display_original'));
        $this->assertTrue(Arr::has($nav, 'sections.content.items.fields::blueprints'));
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

        $expected = [
            'fields::blueprints' => [
                'action' => '@alias',
            ],
            'user::profiles' => [
                'action' => '@move',
            ],
            'tools::utilities::php_info' => [
                'action' => '@alias',
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.content.items'));
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
        $this->assertEquals('Content', Arr::get($nav, 'sections.content.display_original'));
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
            'top_level' => [
                'content::collections::pages' => '@inherit',
                'content::collections::posts' => '@move',
                'content::collections::profiles' => '@inherit',
            ],
        ])['sections']['top_level']['items']));
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
            'top_level' => [
                'reorder' => true,
                'content::collections::pages' => '@inherit',
                'content::collections::posts' => '@move',
                'content::collections::profiles' => '@inherit',
            ],
        ])['sections']['top_level']['items']));

        // With `reorder: true` and sections properly nested
        $this->assertEquals($expected, array_keys($this->normalize([
            'top_level' => [
                'reorder' => true,
                'items' => [
                    'content::collections::pages' => '@inherit',
                    'content::collections::posts' => '@move',
                    'content::collections::profiles' => '@inherit',
                ],
            ],
        ])['sections']['top_level']['items']));
    }

    /** @test */
    public function it_defaults_action_to_alias_when_not_reordering()
    {
        $nav = $this->normalize([
            'top_level' => [
                'content::collections::pages' => [],
            ],
        ]);

        $this->assertEquals('@alias', Arr::get($nav, 'sections.top_level.items.content::collections::pages.action'));
    }

    /** @test */
    public function it_defaults_action_to_inherit_when_reordering()
    {
        $nav = $this->normalize([
            'top_level' => [
                'reorder' => true,
                'content::collections::pages' => [],
            ],
        ]);

        $this->assertEquals('@inherit', Arr::get($nav, 'sections.top_level.items.content::collections::pages.action'));
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
                        'Yaml' => 'https://spamlyaml.org',
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
                'Json' => 'https://jsonvarga.net',
                'Yaml' => 'https://spamlyaml.org',
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.content.items.user::profiles'));
    }

    /** @test */
    public function it_filters_out_create_actions_for_existing_item_ids()
    {
        $this->markTestSkipped();
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
                        'Statamic Dashboard' => '/dashboard',
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
                'Statamic Dashboard' => '/dashboard',
            ],
        ];

        $this->assertEquals($expected, Arr::get($nav, 'sections.top_level.items.top_level::dashboard'));
    }

    /** @test */
    public function it_filters_out_modify_actions_for_items_not_natively_in_section()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function it_normalizes_a_fairly_minimal_example_config()
    {
        $nav = $this->normalize([
            'top_level' => [
                'fields::blueprints' => '@alias',
                'content::collections::pages' => '@move',
            ],
            'content' => [
                'user::profiles' => [
                    'action' => '@create',
                    'url' => '/profiles',
                    'icon' => 'user',
                ],
            ],
        ]);

        $expected = [
            'reorder' => false,
            'sections' => [
                'top_level' => [
                    'reorder' => false,
                    'display' => 'Top Level',
                    'display_original' => null,
                    'items' => [
                        'fields::blueprints' => [
                            'action' => '@alias',
                        ],
                        'content::collections::pages' => [
                            'action' => '@move',
                        ],
                    ],
                ],
                'content' => [
                    'reorder' => false,
                    'display' => 'Content',
                    'display_original' => null,
                    'items' => [
                        'user::profiles' => [
                            'action' => '@create',
                            'url' => '/profiles',
                            'icon' => 'user',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $nav);
    }
}
