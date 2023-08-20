<?php

namespace Tests\CP\Navigation;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CoreNavTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    /** @test */
    public function it_can_build_a_default_nav()
    {
        $expected = collect([
            'Top Level' => ['Dashboard', 'Playground'],
            'Content' => ['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'],
            'Fields' => ['Blueprints', 'Fieldsets'],
            'Tools' => ['Forms', 'Updates', 'Addons', 'Utilities', 'GraphQL'],
            'Users' => ['Users', 'Groups', 'Permissions'],
        ]);

        $this->actingAs(tap(User::make()->makeSuper())->save());

        $nav = $this->build();

        $this->assertEquals($expected->keys(), $nav->keys());
        $this->assertEquals($expected->get('Content'), $nav->get('Content')->map->display()->all());
        $this->assertEquals($expected->get('Fields'), $nav->get('Fields')->map->display()->all());
        $this->assertEquals($expected->get('Tools'), $nav->get('Tools')->map->display()->all());
        $this->assertEquals($expected->get('Users'), $nav->get('Users')->map->display()->all());
    }

    protected function build()
    {
        return Nav::build()->pluck('items', 'display');
    }
}
