<?php

namespace Tests\CP\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
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

    #[Test]
    public function it_can_build_a_default_nav()
    {
        $expected = collect([
            'Top Level' => ['Dashboard', 'Playground'],
            'Content' => ['Collections', 'Navigation', 'Taxonomies', 'Assets', 'Globals'],
            'Fields' => ['Blueprints', 'Fieldsets'],
            'Tools' => ['Forms', 'Updates', 'Addons', 'Utilities', 'GraphQL'],
            'Settings' => ['Site', 'Preferences'],
            'Users' => ['Users', 'Groups', 'Permissions'],
        ]);

        $this->actingAs(tap(User::make()->makeSuper())->save());

        $nav = $this->build();

        $this->assertEquals($expected->keys(), $nav->keys());
        $this->assertEquals($expected->get('Content'), $nav->get('Content')->map->display()->all());
        $this->assertEquals($expected->get('Fields'), $nav->get('Fields')->map->display()->all());
        $this->assertEquals($expected->get('Tools'), $nav->get('Tools')->map->display()->all());
        $this->assertEquals($expected->get('Settings'), $nav->get('Settings')->map->display()->all());
        $this->assertEquals($expected->get('Users'), $nav->get('Users')->map->display()->all());
    }

    #[Test]
    public function it_builds_plural_sites_item_when_multisite_is_enabled()
    {
        Facades\Config::set('statamic.system.multisite', true);

        $this->actingAs(tap(User::make()->makeSuper())->save());

        $nav = $this->build();

        $this->assertEquals(
            ['Sites', 'Preferences'],
            $nav->get('Settings')->map->display()->all()
        );
    }

    #[Test]
    public function it_doesnt_build_collection_children_from_sites_that_the_user_is_not_authorized_to_see()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
            'de' => ['url' => '/', 'locale' => 'de_DE', 'name' => 'German'],
        ]);

        Facades\Collection::make('has_some_french')->sites(['en', 'fr', 'de'])->save();
        Facades\Collection::make('has_no_french')->sites(['en', 'de'])->save();
        Facades\Collection::make('has_only_french')->sites(['fr'])->save();

        $this->setTestRoles(['test' => [
            'access cp',
            'view has_some_french entries',
            'view has_no_french entries',
            'view has_only_french entries',
            'access en site',
            // 'access fr site', // Give them access to all data, but not all sites
            'access de site',
        ]]);

        $this
            ->actingAs(tap(User::make()->assignRole('test'))->save())
            ->get(cp_route('collections.index'));

        $actual = $this->build()->get('Content')->keyBy->display()->get('Collections')->children()->map->id()->all();

        $expected = [
            'content::collections::has_some_french', // Can see, because content type contains data from accessible sites.
            'content::collections::has_no_french', // Can see, because content type only contains data from accessible sites.
            // 'content::collections::has_only_french', // Cannot, because content only contains data from prohibited sites.
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    #[Test]
    public function it_doesnt_build_navigation_children_from_sites_that_the_user_is_not_authorized_to_see()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
            'de' => ['url' => '/', 'locale' => 'de_DE', 'name' => 'German'],
        ]);

        $nav1 = tap(Facades\Nav::make()->handle('has_some_french'))->save();
        $nav1->makeTree('en')->save();
        $nav1->makeTree('fr')->save();
        $nav1->makeTree('de')->save();

        $nav2 = tap(Facades\Nav::make()->handle('has_no_french'))->save();
        $nav2->makeTree('en')->save();
        $nav2->makeTree('de')->save();

        $nav3 = tap(Facades\Nav::make()->handle('has_only_french'))->save();
        $nav3->makeTree('fr')->save();

        $this->setTestRoles(['test' => [
            'access cp',
            'view has_some_french nav',
            'view has_no_french nav',
            'view has_only_french nav',
            'access en site',
            // 'access fr site', // Give them access to all data, but not all sites
            'access de site',
        ]]);

        $this
            ->actingAs(tap(User::make()->assignRole('test'))->save())
            ->get(cp_route('navigation.index'));

        $actual = $this->build()->get('Content')->keyBy->display()->get('Navigation')->children()->map->id()->all();

        $expected = [
            'content::navigation::has_some_french', // Can see, because content type contains data from accessible sites.
            'content::navigation::has_no_french', // Can see, because content type only contains data from accessible sites.
            // 'content::navigation::has_only_french', // Cannot, because content only contains data from prohibited sites.
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    #[Test]
    public function it_doesnt_build_taxonomy_children_from_sites_that_the_user_is_not_authorized_to_see()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
            'de' => ['url' => '/', 'locale' => 'de_DE', 'name' => 'German'],
        ]);

        Facades\Taxonomy::make('has_some_french')->sites(['en', 'fr', 'de'])->save();
        Facades\Taxonomy::make('has_no_french')->sites(['en', 'de'])->save();
        Facades\Taxonomy::make('has_only_french')->sites(['fr'])->save();

        $this->setTestRoles(['test' => [
            'access cp',
            'view has_some_french terms',
            'view has_no_french terms',
            'view has_only_french terms',
            'access en site',
            // 'access fr site', // Give them access to all data, but not all sites
            'access de site',
        ]]);

        $this
            ->actingAs(tap(User::make()->assignRole('test'))->save())
            ->get(cp_route('taxonomies.index'));

        $actual = $this->build()->get('Content')->keyBy->display()->get('Taxonomies')->children()->map->id()->all();

        $expected = [
            'content::taxonomies::has_some_french', // Can see, because content type contains data from accessible sites.
            'content::taxonomies::has_no_french', // Can see, because content type only contains data from accessible sites.
            // 'content::taxonomies::has_only_french', // Cannot, because content only contains data from prohibited sites.
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    #[Test]
    public function it_doesnt_build_globals_children_from_sites_that_the_user_is_not_authorized_to_see()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
            'de' => ['url' => '/', 'locale' => 'de_DE', 'name' => 'German'],
        ]);

        $set1 = Facades\GlobalSet::make('has_some_french')->sites(['en' => null, 'fr' => null, 'de' => null]);
        $set1->save();

        $set2 = Facades\GlobalSet::make('has_no_french')->sites(['en' => null, 'de' => null]);
        $set2->save();

        $set3 = Facades\GlobalSet::make('has_only_french')->sites(['fr' => null]);
        $set3->save();

        $this->setTestRoles(['test' => [
            'access cp',
            'edit has_some_french globals',
            'edit has_no_french globals',
            'edit has_only_french globals',
            'access en site',
            // 'access fr site', // Give them access to all data, but not all sites
            'access de site',
        ]]);

        $this
            ->actingAs(tap(User::make()->assignRole('test'))->save())
            ->get(cp_route('globals.index'));

        $actual = $this->build()->get('Content')->keyBy->display()->get('Globals')->children()->map->id()->all();

        $expected = [
            'content::globals::has_some_french', // Can see, because content type contains data from accessible sites.
            'content::globals::has_no_french', // Can see, because content type only contains data from accessible sites.
            // 'content::globals::has_only_french', // Cannot, because content only contains data from prohibited sites.
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function build()
    {
        return Nav::build()->pluck('items', 'display');
    }
}
