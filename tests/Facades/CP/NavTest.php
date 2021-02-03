<?php

namespace Tests\Facades\CP;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

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

        $nav = Nav::build();

        $this->assertEquals($expected->keys(), $nav->keys());
        $this->assertEquals($expected->get('Content'), $nav->get('Content')->map->name()->all());
        $this->assertEquals($expected->get('Fields'), $nav->get('Fields')->map->name()->all());
        $this->assertEquals($expected->get('Tools'), $nav->get('Tools')->map->name()->all());
        $this->assertEquals($expected->get('Users'), $nav->get('Users')->map->name()->all());
    }

    /** @test */
    public function is_can_create_a_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::utilities('Wordpress Importer')
            ->route('wordpress-importer.index')
            ->can('view updates');

        $item = Nav::build()->get('Utilities')->last();

        $this->assertEquals('Utilities', $item->section());
        $this->assertEquals('Wordpress Importer', $item->name());
        $this->assertEquals(config('app.url').'/wordpress-importer', $item->url());
        $this->assertEquals('view updates', $item->authorization()->ability);
        $this->assertEquals('view updates', $item->can()->ability);
    }

    /** @test */
    public function it_can_more_explicitly_create_a_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::create('R2-D2')
            ->section('Droids')
            ->url('/r2');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('R2-D2', $item->name());
        $this->assertEquals('http://localhost/r2', $item->url());
    }

    /** @test */
    public function it_can_create_a_nav_item_with_a_more_custom_config()
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
        $this->assertEquals('http://localhost/human-cyborg-relations', $item->url());
        $this->assertEquals('cp.nav.importer', $item->view());
        $this->assertEquals('threepio*', $item->active());
        $this->assertEquals('index', $item->authorization()->ability);
        $this->assertEquals('DroidsClass', $item->authorization()->arguments);
    }

    /** @test */
    public function it_can_create_a_nav_item_with_a_bundled_svg_icon()
    {
        File::put(public_path('vendor/statamic/cp/svg/test.svg'), 'the totally real svg');

        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::utilities('Test')->icon('test');

        $item = Nav::build()->get('Utilities')->last();

        $this->assertEquals('the totally real svg', $item->icon());
    }

    /** @test */
    public function it_can_create_a_nav_item_with_a_custom_svg_icon()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::utilities('Test')
            ->icon('<svg><circle cx="50" cy="50" r="40" stroke="black" stroke-width="3" fill="red" /></svg>');

        $item = Nav::build()->get('Utilities')->last();

        $this->assertEquals('<svg><circle cx="50" cy="50" r="40" stroke="black" stroke-width="3" fill="red" /></svg>', $item->icon());
    }

    /** @test */
    public function it_can_get_and_modify_an_existing_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Nav::droids('WAC-47')
            ->url('/pit-droid')
            ->icon('<svg>...</svg>');

        Nav::droids('WAC-47')
            ->url('/d-squad');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('WAC-47', $item->name());
        $this->assertEquals('<svg>...</svg>', $item->icon());
        $this->assertEquals('http://localhost/d-squad', $item->url());
    }

    /** @test */
    public function it_doesnt_build_items_that_the_user_is_not_authorized_to_see()
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
    public function it_can_create_a_nav_item_with_children()
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
    public function it_doesnt_build_children_that_the_user_is_not_authorized_to_see()
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
    public function it_can_create_a_nav_item_with_deferred_children()
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
    public function it_can_remove_a_nav_section()
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
    public function it_can_remove_a_specific_nav_item()
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
    public function it_can_use_extend_to_defer_the_creation_of_a_nav_item_until_build_time()
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
    public function it_can_use_extend_to_remove_a_default_statamic_nav_item()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $nav = Nav::build();

        $this->assertContains('Collections', Nav::build()->get('Content')->map->name());

        Nav::extend(function ($nav) {
            $nav->remove('Content', 'Collections');
        });

        $this->assertNotContains('Collections', Nav::build()->get('Content')->map->name());
    }

    /** @test */
    public function it_checks_if_active()
    {
        $hello = Nav::create('hello')->url('http://localhost/cp/hello');
        $hell = Nav::create('hell')->url('http://localhost/cp/hell');

        Request::swap(Request::create('http://localhost/cp/hell'));
        $this->assertFalse($hello->isActive());
        $this->assertTrue($hell->isActive());

        Request::swap(Request::create('http://localhost/cp/hello'));
        $this->assertTrue($hello->isActive());
        $this->assertFalse($hell->isActive());

        Request::swap(Request::create('http://localhost/cp/hell/test'));
        $this->assertFalse($hello->isActive());
        $this->assertTrue($hell->isActive());

        Request::swap(Request::create('http://localhost/cp/hello/test'));
        $this->assertTrue($hello->isActive());
        $this->assertFalse($hell->isActive());
    }

    /** @test */
    public function it_sets_the_url()
    {
        tap(Nav::create('absolute')->url('http://domain.com'), function ($nav) {
            $this->assertEquals('http://domain.com', $nav->url());
            $this->assertNull($nav->active());
        });

        tap(Nav::create('site-relative')->url('/foo/bar'), function ($nav) {
            $this->assertEquals('http://localhost/foo/bar', $nav->url());
            $this->assertNull($nav->active());
        });

        tap(Nav::create('cp-relative')->url('foo/bar'), function ($nav) {
            $this->assertEquals('http://localhost/cp/foo/bar', $nav->url());
            $this->assertEquals('foo/bar(/(.*)?|$)', $nav->active());
        });
    }

    /** @test */
    public function it_does_not_automatically_add_an_active_pattern_when_setting_url_if_one_is_already_defined()
    {
        $nav = Nav::create('cp-relative')->active('foo.*')->url('foo/bar');
        $this->assertEquals('http://localhost/cp/foo/bar', $nav->url());
        $this->assertEquals('foo.*', $nav->active());
    }
}
