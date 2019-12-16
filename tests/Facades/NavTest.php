<?php

namespace Tests\Facades;

use Tests\TestCase;
use Statamic\Facades\Nav;
use Tests\FakesRoles;
use Statamic\Facades\User;
use Illuminate\Support\Facades\Route;
use Tests\PreventSavingStacheItemsToDisk;

class NavTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    public function setUp(): void
    {
        parent::setUp();

        Route::any('wordpress-importer', ['as' => 'statamic.cp.wordpress-importer.index']);
        Route::any('security-droids', ['as' => 'statamic.cp.security-droids.index']);
    }

    /** @test */
    function it_can_build_a_default_nav()
    {
        $expected = collect([
            'Top Level' => ['Dashboard', 'Playground'],
            'Content' => ['Collections', 'Structures', 'Taxonomies', 'Assets', 'Globals'],
            'Tools' => ['Forms', /*'Updates',*/ 'Utilities'],
            'Users' => ['Users', 'Groups', 'Permissions'],
            'Site' => [/*'Addons',*/ 'Fields']
        ]);

        $this->actingAs(tap(User::make()->makeSuper())->save());

        $nav = Nav::build();

        $this->assertEquals($expected->keys(), $nav->keys());
        $this->assertEquals($expected->get('Content'), $nav->get('Content')->map->name()->all());
        $this->assertEquals($expected->get('Tools'), $nav->get('Tools')->map->name()->all());
        $this->assertEquals($expected->get('Users'), $nav->get('Users')->map->name()->all());
        $this->assertEquals($expected->get('Site'), $nav->get('Site')->map->name()->all());
    }

    /** @test */
    function is_can_create_a_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::utilities('Wordpress Importer')
            ->route('wordpress-importer.index')
            ->icon('up-arrow')
            ->can('view updates');

        $item = Nav::build()->get('Utilities')->last();

        $this->assertEquals('Utilities', $item->section());
        $this->assertEquals('Wordpress Importer', $item->name());
        $this->assertEquals(config('app.url').'/wordpress-importer', $item->url());
        $this->assertEquals(config('app.url').'/wordpress-importer*', $item->active());
        $this->assertEquals('up-arrow', $item->icon());
        $this->assertEquals('view updates', $item->authorization()->ability);
        $this->assertEquals('view updates', $item->can()->ability);
    }

    /** @test */
    function it_can_more_explicitly_create_a_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::create('R2-D2')
            ->section('Droids')
            ->url('/r2');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('R2-D2', $item->name());
        $this->assertEquals('/r2', $item->url());
    }

    /** @test */
    function it_can_create_a_nav_item_with_a_more_custom_config()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::droids('C-3PO')
            ->active('threepio*')
            ->url('/human-cyborg-relations')
            ->view('cp.nav.importer')
            ->can('index', 'DroidsClass');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('C-3PO', $item->name());
        $this->assertEquals('/human-cyborg-relations', $item->url());
        $this->assertEquals('cp.nav.importer', $item->view());
        $this->assertEquals('threepio*', $item->active());
        $this->assertEquals('index', $item->authorization()->ability);
        $this->assertEquals('DroidsClass', $item->authorization()->arguments);
    }

    /** @test */
    function it_can_get_and_modify_an_existing_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::droids('WAC-47')
            ->url('/pit-droid')
            ->icon('droid');

        Nav::droids('WAC-47')
            ->url('/d-squad');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('WAC-47', $item->name());
        $this->assertEquals('droid', $item->icon());
        $this->assertEquals('/d-squad', $item->url());
    }

    /** @test */
    function it_doesnt_build_items_that_the_user_is_not_authorized_to_see()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $this->actingAs(tap(User::make()->assignRole('test'))->save());

        Nav::theEmpire('Death Star');

        $item = Nav::build()->get('The Empire')->first();

        $this->assertEquals('Death Star', Nav::build()->get('The Empire')->first()->name());

        Nav::theEmpire('Death Star')
            ->can('view death star');

        $this->assertNull(Nav::build()->get('The Empire'));
    }

    /** @test */
    function it_can_create_a_nav_item_with_children()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::droids('Battle Droids')
            ->url('/battle-droids')
            ->children([
                Nav::item('B1')->url('/b1'),
                Nav::item('B2')->url('/b2'),
                'HK-47' => '/hk-47', // If only specifying name and URL, can pass key/value pair as well.
            ]);

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Battle Droids', $item->name());
        $this->assertEquals('B1', $item->children()->get(0)->name());
        $this->assertEquals('B2', $item->children()->get(1)->name());
        $this->assertEquals('HK-47', $item->children()->get(2)->name());
    }

    /** @test */
    function it_doesnt_build_children_that_the_user_is_not_authorized_to_see()
    {
        $this->setTestRoles(['sith' => ['view sith diaries']]);
        $this->actingAs(tap(User::make()->assignRole('sith'))->save());

        Nav::custom('Diaries')
            ->url('/diaries')
            ->children([
                Nav::item('Jedi')->url('/b1')->can('view jedi diaries'),
                Nav::item('Sith')->url('/b2')->can('view sith diaries'),
            ]);

        Nav::custom('Logs')
            ->url('/logs')
            ->children([
                Nav::item('Jedi')->url('/b1')->can('view jedi logs'),
                Nav::item('Sith')->url('/b2')->can('view sith logs'),
            ]);

        $diaries = Nav::build()->get('Custom')->first();
        $logs = Nav::build()->get('Custom')->last();

        $this->assertCount(1, $diaries->children());
        $this->assertEquals('Sith', $diaries->children()->get(0)->name());

        $this->assertNull($logs->children());
    }

    /** @test */
    function it_can_create_a_nav_item_with_deferred_children()
    {
        $this->markTestSkipped('Getting a NotFoundHttpException, even though I\'m registering route?');

        $this
            ->actingAs(User::make()->makeSuper())
            ->get(cp_route('security-droids.index'))
            ->assertStatus(200);

        $item = Nav::droids('Security Droids')
            ->url('/security-droids')
            ->children(function () {
                return [
                    'IG-86' => '/ig-86',
                    'K-2SO' => '/k-2so',
                ];
            });

        $this->assertEquals('Security Droids', $item->name());
        $this->assertTrue(is_callable($item->children()));

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Security Droids', $item->name());
        $this->assertFalse(is_callable($item->children()));
        $this->assertEquals('IG-86', $item->children()->get(0)->name());
        $this->assertEquals('K-2SO', $item->children()->get(1)->name());
    }

    /** @test */
    function it_can_remove_a_nav_section()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::ships('Millenium Falcon')
            ->url('/millenium-falcon')
            ->icon('falcon');

        Nav::ships('X-Wing')
            ->url('/x-wing')
            ->icon('x-wing');

        $this->assertCount(2, Nav::build()->get('Ships'));

        Nav::remove('Ships');

        $this->assertNull(Nav::build()->get('Ships'));
    }

    /** @test */
    function it_can_remove_a_specific_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::ships('Y-Wing')
            ->url('/y-wing')
            ->icon('y-wing');

        Nav::ships('A-Wing')
            ->url('/a-wing')
            ->icon('a-wing');

        $this->assertCount(2, Nav::build()->get('Ships'));

        Nav::remove('Ships', 'Y-Wing');

        $this->assertCount(1, $ships = Nav::build()->get('Ships'));
        $this->assertEquals('A-Wing', $ships->first()->name());
    }

    /** @test */
    function it_can_use_extend_to_defer_the_creation_of_a_nav_item_until_build_time()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::extend(function ($nav) {
            $nav->jedi('Yoda')->url('/yodas-hut')->icon('green-but-cute-alien');
        });

        $this->assertEmpty(Nav::items());

        $nav = Nav::build();

        $this->assertNotEmpty(Nav::items());
        $this->assertContains('Yoda', Nav::build()->get('Jedi')->map->name());
    }

    /** @test */
    function it_can_use_extend_to_remove_a_default_statamic_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $nav = Nav::build();

        $this->assertContains('Collections', Nav::build()->get('Content')->map->name());

        Nav::extend(function ($nav) {
            $nav->remove('Content', 'Collections');
        });

        $this->assertNotContains('Collections', Nav::build()->get('Content')->map->name());
    }
}
