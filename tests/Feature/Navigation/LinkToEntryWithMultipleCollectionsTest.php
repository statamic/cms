<?php

namespace Tests\Feature\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LinkToEntryWithMultipleCollectionsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_can_view_navigation_with_collections_as_array()
    {
        Collection::make('pages')->save();
        Collection::make('articles')->save();

        $nav = tap(Nav::make('test')->collections(['pages', 'articles']))->save();
        $nav->makeTree('default')->save();

        $this->setTestRoles(['test' => ['access cp', 'view test nav']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this->actingAs($user)->get(cp_route('navigation.show', 'test'));

        $response->assertOk();
        $props = $response->viewData('page')['props'];
        $this->assertIsArray($props['collections']);
        $this->assertEquals(['pages', 'articles'], $props['collections']);
    }

    #[Test]
    public function it_can_view_navigation_with_collections_as_string()
    {
        Collection::make('pages')->save();

        // Simulate what happens when YAML has `collections: pages` instead of `collections: [pages]`
        // The NavigationStore passes the raw YAML value (string) to the collections() setter
        $nav = Nav::make('test');
        $nav->collections('pages'); // Pass string directly
        $nav->save();
        $nav->makeTree('default')->save();

        $this->setTestRoles(['test' => ['access cp', 'view test nav']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this->actingAs($user)->get(cp_route('navigation.show', 'test'));

        $response->assertOk();
        $props = $response->viewData('page')['props'];
        $this->assertIsArray($props['collections']);
        $this->assertEquals(['pages'], $props['collections']);
    }

    #[Test]
    public function it_can_edit_navigation_with_collections_as_string()
    {
        Collection::make('pages')->save();

        // When a Nav is loaded from YAML with `collections: pages` (string),
        // the NavigationStore passes that string to the collections() setter
        $nav = Nav::make('test');
        $nav->collections('pages'); // Pass string directly
        $nav->save();

        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this->actingAs($user)->get(cp_route('navigation.edit', 'test'));

        $response->assertOk();
        $this->assertIsArray($response->json('data.values.collections'));
        $this->assertEquals(['pages'], $response->json('data.values.collections'));
    }

    #[Test]
    public function it_can_get_page_selector_filters_when_navigation_has_multiple_collections()
    {
        Collection::make('pages')->save();
        Collection::make('articles')->save();

        // Test the full flow: navigation with multiple collections -> PageSelector -> filters endpoint
        $this->setTestRoles(['test' => ['access cp', 'view pages entries', 'view articles entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['pages', 'articles'], // Array of two
        ]));

        $response = $this->actingAs($user)->getJson("/cp/fieldtypes/relationship/filters?config={$config}");
        
        $response->assertOk();
        $handles = collect($response->json())->pluck('handle');
        $this->assertTrue($handles->contains('collection'));
    }
}
