<?php

namespace Tests\CommandPalette;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CommandPalette\Category;
use Statamic\CommandPalette\ContentSearchResult;
use Statamic\Facades;
use Statamic\Facades\CommandPalette;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CommandPaletteTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    public function setUp(): void
    {
        parent::setUp();

        // TODO: Other tests are leaving behind forms without titles that are causing failures here?
        Facades\Form::shouldReceive('all')->andReturn(collect());
    }

    #[Test]
    public function it_builds_an_array_that_can_be_converted_to_json()
    {
        // Todo: Fix
        $this->markTestSkipped();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('dashboard'))
            ->assertStatus(200);

        $commands = CommandPalette::build();

        $this->assertTrue(is_array($commands));
        $this->assertTrue(is_string(json_encode($commands)));
    }

    #[Test]
    public function it_can_build_commands_off_nav_items()
    {
        // TODO: Fix and flesh out coverage for nav children.
        $this->markTestSkipped();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('dashboard'))
            ->assertStatus(200);

        $navigationCommands = collect(CommandPalette::build())
            ->filter(fn ($item) => $item['category'] === 'Navigation')
            ->map(fn ($item) => Arr::except($item, 'category'))
            ->all();

        $expected = [
            ['text' => 'Top Level > Dashboard', 'url' => 'http://localhost/cp/dashboard'],
            ['text' => 'Content > Collections', 'url' => 'http://localhost/cp/collections'],
            ['text' => 'Content > Navigation', 'url' => 'http://localhost/cp/navigation'],
            ['text' => 'Content > Taxonomies', 'url' => 'http://localhost/cp/taxonomies'],
            ['text' => 'Content > Assets', 'url' => 'http://localhost/cp/assets'],
            ['text' => 'Content > Globals', 'url' => 'http://localhost/cp/globals'],
            ['text' => 'Fields > Blueprints', 'url' => 'http://localhost/cp/fields/blueprints'],
            ['text' => 'Fields > Fieldsets', 'url' => 'http://localhost/cp/fields/fieldsets'],
            ['text' => 'Tools > Forms', 'url' => 'http://localhost/cp/forms'],
            ['text' => 'Tools > Updates', 'url' => 'http://localhost/cp/updater'],
            ['text' => 'Tools > Addons', 'url' => 'http://localhost/cp/addons'],
            ['text' => 'Tools > Utilities', 'url' => 'http://localhost/cp/utilities'],
            ['text' => 'Tools > GraphQL', 'url' => 'http://localhost/cp/graphql'],
            ['text' => 'Settings > Site', 'url' => 'http://localhost/cp/sites'],
            ['text' => 'Settings > Preferences', 'url' => 'http://localhost/cp/preferences'],
            ['text' => 'Users > Users', 'url' => 'http://localhost/cp/users'],
            ['text' => 'Users > Groups', 'url' => 'http://localhost/cp/user-groups'],
            ['text' => 'Users > Permissions', 'url' => 'http://localhost/cp/roles'],
        ];

        $this->assertEquals($expected, $navigationCommands);
    }

    #[Test]
    public function it_can_build_custom_command_items()
    {
        // Simple default miscellaneous command example
        CommandPalette::add('Ask Jeeves', 'https://ask.com');

        // More advanced config example
        CommandPalette::add(
            text: 'Hotbot',
            url: 'https://hotbot.com',
            openNewTab: true,
            trackRecent: false,
            icon: 'sexy-robot',
            category: Category::Actions,
            // keys: ... // TODO: test custom hotkey config when we set that up
        );

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('dashboard'))
            ->assertStatus(200);

        $miscCommands = collect(CommandPalette::build())
            ->filter(fn ($item) => in_array($item['category'], ['Actions', 'Miscellaneous']))
            ->all();

        $expected = [
            [
                'category' => 'Miscellaneous',
                'text' => 'Ask Jeeves',
                'url' => 'https://ask.com',
                'openNewTab' => false,
                'trackRecent' => true,
                'icon' => 'entry',
                'keys' => null,
            ],
            [
                'category' => 'Actions',
                'text' => 'Hotbot',
                'url' => 'https://hotbot.com',
                'openNewTab' => true,
                'trackRecent' => false,
                'icon' => 'sexy-robot',
                'keys' => null,
            ],
        ];

        $this->assertArraySubset($expected, $miscCommands);
    }

    #[Test]
    public function it_can_build_command_with_array_based_text_for_rendering_arrow_separators_in_js()
    {
        CommandPalette::add(['Preferences', 'Best Website', 'Ask Jeeves'], 'https://ask.com');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('dashboard'))
            ->assertStatus(200);

        $miscCommands = collect(CommandPalette::build())
            ->filter(fn ($item) => $item['category'] === 'Miscellaneous')
            ->all();

        $expected = [
            [
                'category' => 'Miscellaneous',
                'text' => ['Preferences', 'Best Website', 'Ask Jeeves'],
                'url' => 'https://ask.com',
                'openNewTab' => false,
                'icon' => 'entry',
                'keys' => null,
            ],
        ];

        $this->assertArraySubset($expected, $miscCommands);
    }

    #[Test]
    public function it_tracks_recent_content_search_results_by_default()
    {
        $searchResult = new ContentSearchResult('Articles', Category::Search);

        $this->assertTrue($searchResult->toArray()['trackRecent']);
    }
}
