<?php

namespace Tests\CommandPalette;

use PHPUnit\Framework\Attributes\Test;
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
            ['type' => 'link', 'text' => 'Top Level > Dashboard', 'url' => 'http://localhost/cp/dashboard'],
            ['type' => 'link', 'text' => 'Content > Collections', 'url' => 'http://localhost/cp/collections'],
            ['type' => 'link', 'text' => 'Content > Navigation', 'url' => 'http://localhost/cp/navigation'],
            ['type' => 'link', 'text' => 'Content > Taxonomies', 'url' => 'http://localhost/cp/taxonomies'],
            ['type' => 'link', 'text' => 'Content > Assets', 'url' => 'http://localhost/cp/assets'],
            ['type' => 'link', 'text' => 'Content > Globals', 'url' => 'http://localhost/cp/globals'],
            ['type' => 'link', 'text' => 'Fields > Blueprints', 'url' => 'http://localhost/cp/fields/blueprints'],
            ['type' => 'link', 'text' => 'Fields > Fieldsets', 'url' => 'http://localhost/cp/fields/fieldsets'],
            ['type' => 'link', 'text' => 'Tools > Forms', 'url' => 'http://localhost/cp/forms'],
            ['type' => 'link', 'text' => 'Tools > Updates', 'url' => 'http://localhost/cp/updater'],
            ['type' => 'link', 'text' => 'Tools > Addons', 'url' => 'http://localhost/cp/addons'],
            ['type' => 'link', 'text' => 'Tools > Utilities', 'url' => 'http://localhost/cp/utilities'],
            ['type' => 'link', 'text' => 'Tools > GraphQL', 'url' => 'http://localhost/cp/graphql'],
            ['type' => 'link', 'text' => 'Settings > Site', 'url' => 'http://localhost/cp/sites'],
            ['type' => 'link', 'text' => 'Settings > Preferences', 'url' => 'http://localhost/cp/preferences'],
            ['type' => 'link', 'text' => 'Users > Users', 'url' => 'http://localhost/cp/users'],
            ['type' => 'link', 'text' => 'Users > Groups', 'url' => 'http://localhost/cp/user-groups'],
            ['type' => 'link', 'text' => 'Users > Permissions', 'url' => 'http://localhost/cp/roles'],
        ];

        $this->assertEquals($expected, $navigationCommands);
    }
}
