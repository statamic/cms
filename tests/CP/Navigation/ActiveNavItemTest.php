<?php

namespace Tests\CP\Navigation;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Navigation\NavBuilder;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ActiveNavItemTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    public function setUp(): void
    {
        parent::setUp();

        // Always act as super user for these tests
        $this->actingAs(tap(User::make()->makeSuper())->save());

        // TODO: Other tests are leaving behind forms without titles that are causing failures here?
        Facades\Form::shouldReceive('all')->andReturn(collect());
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Set up test routes for fake SEO Pro extension
        $app->booted(function () {
            Route::get('cp/seo-pro', fn () => 'test');
            Route::get('cp/seo-pro/section-defaults', fn () => 'test');
            Route::get('cp/seo-pro/section-defaults/pages', fn () => 'test');
            Route::get('cp/seo-pro/section-defaults/articles', fn () => 'test');
            Route::get('cp/totally-custom-url', fn () => 'test');
            Route::get('cp/totally-custom-url/deeper/descendant', fn () => 'test');
        });
    }

    #[Test]
    public function it_resolves_all_children_only_once_to_build_caches_for_is_active_checks()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        // Clear caches
        Nav::clearCachedUrls();
        $this->assertFalse(Cache::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertFalse(Blink::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertFalse(Cache::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertFalse(Blink::has(NavBuilder::ALL_URLS_CACHE_KEY));

        // Ensure that all children are resolved and URLs are cached for `isActive()` checks on first build
        $nav = Nav::build()->pluck('items', 'display');
        $this->assertTrue(Cache::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertTrue(Blink::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertTrue(Cache::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertTrue(Blink::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertInstanceOf(Collection::class, $this->getItemByDisplay($nav->get('Content'), 'Collections')->children());
        $this->assertInstanceOf(Collection::class, $this->getItemByDisplay($nav->get('Content'), 'Taxonomies')->children());

        // Ensure that it builds children as unresolved closures on second build
        $nav = Nav::build()->pluck('items', 'display');
        $this->assertTrue(Cache::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertTrue(Blink::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertTrue(Cache::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertTrue(Blink::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertInstanceOf(Closure::class, $this->getItemByDisplay($nav->get('Content'), 'Collections')->children());
        $this->assertInstanceOf(Closure::class, $this->getItemByDisplay($nav->get('Content'), 'Taxonomies')->children());
    }

    #[Test]
    public function it_updates_caches_when_new_child_urls_are_detected_after_resolving_children()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        // Ensure we clear cached URLs and build nav cache
        Nav::clearCachedUrls();
        Nav::build();

        // Assert that our collection children are properly cached
        $collectionsChildrenUrls = [
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
        ];
        $this->assertEquals($collectionsChildrenUrls, Cache::get(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY)->get('content::collections'));
        $this->assertEquals($collectionsChildrenUrls, Blink::get(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY)->get('content::collections'));
        collect($collectionsChildrenUrls)->each(function ($url) {
            $this->assertTrue(Cache::get(NavBuilder::ALL_URLS_CACHE_KEY)->contains($url));
            $this->assertTrue(Blink::get(NavBuilder::ALL_URLS_CACHE_KEY)->contains($url));
        });

        // Now let's create a new collection
        Facades\Collection::make('products')->title('Products')->save();

        // Simply building the nav should change what is cached
        $collectionsChildrenUrls = [
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
        ];
        $this->assertEquals($collectionsChildrenUrls, Cache::get(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY)->get('content::collections'));
        $this->assertEquals($collectionsChildrenUrls, Blink::get(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY)->get('content::collections'));
        collect($collectionsChildrenUrls)->each(function ($url) {
            $this->assertTrue(Cache::get(NavBuilder::ALL_URLS_CACHE_KEY)->contains($url));
            $this->assertTrue(Blink::get(NavBuilder::ALL_URLS_CACHE_KEY)->contains($url));
        });

        // But if we build the nav again by hitting collections url to resolve its' children, the caches should get updated
        $this
            ->get('http://localhost/cp/collections')
            ->assertStatus(200);

        // Assert that our collection children caches are properly updated
        $updatedChildrenUrls = [
            'http://localhost/cp/collections/articles',
            'http://localhost/cp/collections/pages',
            'http://localhost/cp/collections/products',
        ];
        $this->assertEquals($updatedChildrenUrls, Cache::get(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY)->get('content::collections'));
        $this->assertEquals($updatedChildrenUrls, Blink::get(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY)->get('content::collections'));
        collect($updatedChildrenUrls)->each(function ($url) {
            $this->assertTrue(Cache::get(NavBuilder::ALL_URLS_CACHE_KEY)->contains($url));
            $this->assertTrue(Blink::get(NavBuilder::ALL_URLS_CACHE_KEY)->contains($url));
        });
    }

    #[Test]
    public function it_builds_core_children_closure_when_not_active()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/dashboard')
            ->assertStatus(200);

        $collections = $this->buildAndGetItem('Content', 'Collections');

        $this->assertFalse($collections->isActive());
        $this->assertInstanceOf(Closure::class, $collections->children());
    }

    #[Test]
    public function it_resolves_core_children_closure_and_can_check_when_parent_item_is_active()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections')
            ->assertStatus(200);

        $collections = $this->buildAndGetItem('Content', 'Collections');

        $this->assertTrue($collections->isActive());
        $this->assertInstanceOf(Collection::class, $collections->children());
        $this->assertFalse($this->getItemByDisplay($collections->children(), 'Pages')->isActive());
        $this->assertFalse($this->getItemByDisplay($collections->children(), 'Articles')->isActive());
    }

    #[Test]
    public function it_resolves_core_children_closure_and_can_check_when_parent_and_child_item_are_active()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/articles')
            ->assertStatus(200);

        $collections = $this->buildAndGetItem('Content', 'Collections');

        $this->assertTrue($collections->isActive());
        $this->assertInstanceOf(Collection::class, $collections->children());
        $this->assertFalse($this->getItemByDisplay($collections->children(), 'Pages')->isActive());
        $this->assertTrue($this->getItemByDisplay($collections->children(), 'Articles')->isActive());
    }

    #[Test]
    public function it_resolves_core_children_closure_and_can_check_when_parent_and_descendant_of_parent_item_is_active()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/create')
            ->assertStatus(200);

        $collections = $this->buildAndGetItem('Content', 'Collections');

        $this->assertTrue($collections->isActive());
        $this->assertInstanceOf(Collection::class, $collections->children());
        $this->assertFalse($collections->children()->keyBy->display()->get('Pages')->isActive());
        $this->assertFalse($collections->children()->keyBy->display()->get('Articles')->isActive());
    }

    #[Test]
    public function it_resolves_core_children_closure_and_can_check_when_parent_and_descendant_of_child_item_is_active()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/articles/entries/create/en')
            ->assertStatus(200);

        $collections = $this->buildAndGetItem('Content', 'Collections');

        $this->assertTrue($collections->isActive());
        $this->assertInstanceOf(Collection::class, $collections->children());
        $this->assertFalse($collections->children()->keyBy->display()->get('Pages')->isActive());
        $this->assertTrue($collections->children()->keyBy->display()->get('Articles')->isActive());
    }

    #[Test]
    public function it_can_check_if_parent_extension_with_array_based_children_item_is_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children([
                    $nav->item('Reports')->url('/cp/seo-pro/reports')->can('view seo reports'),
                    $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                ]);
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/seo-pro')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertTrue($seoPro->isActive());
        $this->assertInstanceOf(Collection::class, $seoPro->children());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Reports')->isActive());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Section Defaults')->isActive());
    }

    #[Test]
    public function it_can_check_when_parent_and_array_based_child_extension_items_are_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children([
                    $nav->item('Reports')->url('/cp/seo-pro/reports')->can('view seo reports'),
                    $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                ]);
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/seo-pro/section-defaults')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertTrue($seoPro->isActive());
        $this->assertInstanceOf(Collection::class, $seoPro->children());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Reports')->isActive());
        $this->assertTrue($this->getItemByDisplay($seoPro->children(), 'Section Defaults')->isActive());
    }

    #[Test]
    public function it_can_check_when_parent_and_array_based_descendant_of_child_extension_item_is_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children([
                    $nav->item('Reports')->url('/cp/seo-pro/reports')->can('view seo reports'),
                    $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                ]);
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/seo-pro/section-defaults/pages')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertTrue($seoPro->isActive());
        $this->assertInstanceOf(Collection::class, $seoPro->children());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Reports')->isActive());
        $this->assertTrue($this->getItemByDisplay($seoPro->children(), 'Section Defaults')->isActive());
    }

    #[Test]
    public function it_builds_extension_children_closure_when_not_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Reports')->url('/cp/seo-pro/')->can('view seo reports'),
                        $nav->item('Site Defaults')->url('/cp/seo-pro/site-defaults')->can('edit seo site defaults'),
                        $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/dashboard')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertFalse($seoPro->isActive());
        $this->assertInstanceOf(Closure::class, $seoPro->children());
    }

    #[Test]
    public function it_resolves_extension_children_closure_and_can_check_when_parent_item_is_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Reports')->url('/cp/seo-pro/reports')->can('view seo reports'),
                        $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/seo-pro')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertTrue($seoPro->isActive());
        $this->assertInstanceOf(Collection::class, $seoPro->children());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Reports')->isActive());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Section Defaults')->isActive());
    }

    #[Test]
    public function it_resolves_extension_children_closure_and_can_check_when_parent_and_child_item_are_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Reports')->url('/cp/seo-pro/reports')->can('view seo reports'),
                        $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/seo-pro/section-defaults')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertTrue($seoPro->isActive());
        $this->assertInstanceOf(Collection::class, $seoPro->children());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Reports')->isActive());
        $this->assertTrue($this->getItemByDisplay($seoPro->children(), 'Section Defaults')->isActive());
    }

    #[Test]
    public function it_resolves_extension_children_closure_and_can_check_when_parent_and_descendant_of_child_item_is_active()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->url('/cp/seo-pro')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Reports')->url('/cp/seo-pro/reports')->can('view seo reports'),
                        $nav->item('Section Defaults')->url('/cp/seo-pro/section-defaults')->can('edit seo section defaults'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/seo-pro/section-defaults/pages')
            ->assertStatus(200);

        $seoPro = $this->buildAndGetItem('Tools', 'SEO Pro');

        $this->assertTrue($seoPro->isActive());
        $this->assertInstanceOf(Collection::class, $seoPro->children());
        $this->assertFalse($this->getItemByDisplay($seoPro->children(), 'Reports')->isActive());
        $this->assertTrue($this->getItemByDisplay($seoPro->children(), 'Section Defaults')->isActive());
    }

    #[Test]
    public function it_properly_handles_various_edge_cases_when_checking_is_active_on_descendants_of_nav_children()
    {
        // Ensure urls are not cached so that we can test regex based isActive() checks
        Nav::clearCachedUrls();

        // These patterns are only intended to check against descendants of child items, since we have explicit child URLs
        $parent = Nav::create('parent')
            ->section('test')
            ->url('http://localhost/cp/parent')
            ->children([
                $hello = Nav::create('hello')->url('http://localhost/cp/hello'),
                $helloWithQueryParams = Nav::create('helloWithAnchor')->url('http://localhost/cp/hello?params'),
                $helloWithAnchor = Nav::create('helloWithAnchor')->url('http://localhost/cp/hello#anchor'),
                $hell = Nav::create('hell')->url('http://localhost/cp/hell'),
                $localNotCp = Nav::create('localNotCp')->url('/dashboard'),
                $external = Nav::create('external')->url('http://external.com'),
                $externalSecure = Nav::create('externalSecure')->url('https://external.com'),
            ]);

        // Test active status on an explicit item
        Request::swap(Request::create('http://localhost/cp/hell'));
        $this->assertTrue($parent->isActive());
        $this->assertFalse($hello->isActive());
        $this->assertFalse($helloWithQueryParams->isActive());
        $this->assertFalse($helloWithAnchor->isActive());
        $this->assertTrue($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Test active status on an explicit item where url params or anchors were set on url
        Request::swap(Request::create('http://localhost/cp/hello'));
        $this->assertTrue($parent->isActive());
        $this->assertTrue($hello->isActive());
        $this->assertTrue($helloWithQueryParams->isActive());
        $this->assertTrue($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Test active status on a descendant of an explicit item
        Request::swap(Request::create('http://localhost/cp/hell/test'));
        $this->assertTrue($parent->isActive());
        $this->assertFalse($hello->isActive());
        $this->assertFalse($helloWithQueryParams->isActive());
        $this->assertFalse($helloWithAnchor->isActive());
        $this->assertTrue($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Test active status on a descendant of an explicit item where url params or anchors were set on url
        Request::swap(Request::create('http://localhost/cp/hello/test'));
        $this->assertTrue($parent->isActive());
        $this->assertTrue($hello->isActive());
        $this->assertTrue($helloWithQueryParams->isActive());
        $this->assertTrue($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Test active status on a descendant of an explicit item where url param is part of current url
        Request::swap(Request::create('http://localhost/cp/hello?params'));
        $this->assertTrue($parent->isActive());
        $this->assertTrue($hello->isActive());
        $this->assertTrue($helloWithQueryParams->isActive());
        $this->assertTrue($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Test active status on a descendant of an explicit item where anchor is part of current url
        Request::swap(Request::create('http://localhost/cp/hello#anchor'));
        $this->assertTrue($parent->isActive());
        $this->assertTrue($hello->isActive());
        $this->assertTrue($helloWithQueryParams->isActive());
        $this->assertTrue($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Test active status on a deeper descendant of an explicit item where url params and anchors were set on url
        Request::swap(Request::create('http://localhost/cp/hello/this/is/super/nested?params#anchor'));
        $this->assertTrue($parent->isActive());
        $this->assertTrue($hello->isActive());
        $this->assertTrue($helloWithQueryParams->isActive());
        $this->assertTrue($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Ensure regex check is not used when checking is active on explicit parent item
        Request::swap(Request::create('http://localhost/cp/parent'));
        $this->assertTrue($parent->isActive());
        $this->assertFalse($hello->isActive());
        $this->assertFalse($helloWithQueryParams->isActive());
        $this->assertFalse($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());

        // Ensure regex check is not used when checking is active on descendant of parent item
        Request::swap(Request::create('http://localhost/cp/parent/nested/item'));
        $this->assertFalse($parent->isActive());
        $this->assertFalse($hello->isActive());
        $this->assertFalse($helloWithQueryParams->isActive());
        $this->assertFalse($helloWithAnchor->isActive());
        $this->assertFalse($hell->isActive());
        $this->assertFalse($localNotCp->isActive());
        $this->assertFalse($external->isActive());
        $this->assertFalse($externalSecure->isActive());
    }

    #[Test]
    public function active_nav_descendant_url_still_functions_properly_when_parent_item_has_no_children()
    {
        Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('Schopify')->url('/cp/totally-custom-url');
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/totally-custom-url/deeper/descendant')
            ->assertStatus(200);

        $toolsItems = $this->build()->get('Tools');

        $this->assertTrue($this->getItemByDisplay($toolsItems, 'Schopify')->isActive());
        $this->assertFalse($this->getItemByDisplay($toolsItems, 'Addons')->isActive());
        $this->assertFalse($this->getItemByDisplay($toolsItems, 'Utilities')->isActive());
    }

    #[Test]
    public function active_nav_check_still_functions_properly_when_custom_nav_extension_hijacks_a_core_item_child()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();
        Facades\Collection::make('products')->title('Products')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        // Remove `Products` and `Categories` from core parents, and add to `Schopify` extension item as children
        Facades\CP\Nav::extend(function ($nav) {
            $nav->remove('Content', 'Collections', 'Products');
            $nav->remove('Content', 'Taxonomies', 'Categories');

            $nav->tools('Schopify')
                ->url('/cp/collections/products')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Products')->url('/cp/collections/products'),
                        $nav->item('Categories')->url('/cp/taxonomies/categories'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/products')
            ->assertStatus(200);

        $nav = $this->build();

        $collections = $this->getItemByDisplay($nav->get('Content'), 'Collections');
        $taxonomies = $this->getItemByDisplay($nav->get('Content'), 'Taxonomies');
        $schopify = $this->getItemByDisplay($nav->get('Tools'), 'Schopify');

        // Ensure only the `Schopify` nav item is active, since we moved the current url (ie. `Products`) to this item
        $this->assertFalse($collections->isActive());
        $this->assertFalse($taxonomies->isActive());
        $this->assertTrue($schopify->isActive());
        $this->assertInstanceOf(Collection::class, $schopify->children());

        // Ensure the new `Products` child under `Schopify` is active
        $this->assertTrue($this->getItemByDisplay($schopify->children(), 'Products')->isActive());
        $this->assertFalse($this->getItemByDisplay($schopify->children(), 'Categories')->isActive());

        // Ensure hijacked items were properly removed from original parents
        $this->assertInstanceOf(Collection::class, $collections->children());
        $this->assertEquals(['Articles', 'Pages'], $collections->children()->map->display()->all());
        $this->assertInstanceOf(Collection::class, $taxonomies->children());
        $this->assertEquals(['Tags'], $taxonomies->children()->map->display()->all());
    }

    #[Test]
    public function active_nav_descendant_check_still_functions_properly_when_custom_nav_extension_hijacks_a_core_item_child()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();
        Facades\Collection::make('products')->title('Products')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        // Remove `Products` and `Categories` from core parents, and add to `Schopify` extension item as children
        Facades\CP\Nav::extend(function ($nav) {
            $nav->remove('Content', 'Collections', 'Products');
            $nav->remove('Content', 'Taxonomies', 'Categories');

            $nav->tools('Schopify')
                ->url('/cp/collections/products')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Products')->url('/cp/collections/products'),
                        $nav->item('Categories')->url('/cp/taxonomies/categories'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/products/entries/create/en')
            ->assertStatus(200);

        $nav = $this->build();

        $collections = $this->getItemByDisplay($nav->get('Content'), 'Collections');
        $taxonomies = $this->getItemByDisplay($nav->get('Content'), 'Taxonomies');
        $schopify = $this->getItemByDisplay($nav->get('Tools'), 'Schopify');

        // Ensure only the `Schopify` nav item is active, since we moved the current url (ie. `Products`) to this item
        $this->assertFalse($collections->isActive());
        $this->assertFalse($taxonomies->isActive());
        $this->assertTrue($schopify->isActive());
        $this->assertInstanceOf(Collection::class, $schopify->children());

        // Ensure the new `Products` child under `Schopify` is active, because the current URL is a descendant of this item
        $this->assertTrue($this->getItemByDisplay($schopify->children(), 'Products')->isActive());
        $this->assertFalse($this->getItemByDisplay($schopify->children(), 'Categories')->isActive());
    }

    #[Test]
    public function active_nav_descendant_with_unrelated_url_still_functions_properly_when_custom_nav_extension_hijacks_a_core_item_child()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();
        Facades\Collection::make('products')->title('Products')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        // Remove `Products` and `Categories` from core parents, and add to `Schopify` extension item as children
        Facades\CP\Nav::extend(function ($nav) {
            $nav->remove('Content', 'Collections', 'Products');
            $nav->remove('Content', 'Taxonomies', 'Categories');

            $nav->tools('Schopify')
                ->url('/cp/collections/products')
                ->children(function () use ($nav) {
                    return [
                        $nav->item('Products')->url('/cp/collections/products'),
                        $nav->item('Categories')->url('/cp/taxonomies/categories'),
                        $nav->item('Unrelated')->url('/cp/totally-custom-url'),
                    ];
                });
        });

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/totally-custom-url/deeper/descendant')
            ->assertStatus(200);

        $schopify = $this->buildAndGetItem('Tools', 'Schopify');

        // Ensure only the `Schopify` nav item is active and children are resolved
        $this->assertTrue($schopify->isActive());
        $this->assertInstanceOf(Collection::class, $schopify->children());

        // Ensure our `Unrelated` totally custom URL item is considered active as well, based on URL hierarchy
        $this->assertFalse($this->getItemByDisplay($schopify->children(), 'Products')->isActive());
        $this->assertFalse($this->getItemByDisplay($schopify->children(), 'Categories')->isActive());
        $this->assertTrue($this->getItemByDisplay($schopify->children(), 'Unrelated')->isActive());
    }

    #[Test]
    public function active_nav_check_still_functions_properly_on_moved_items()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/articles')
            ->assertStatus(200);

        $nav = $this->build([
            'top_level' => [
                'content::collections::articles' => [
                    'action' => '@move',
                    'children' => [
                        'content::taxonomies::categories' => '@move',
                    ],
                ],
            ],
        ]);

        $articles = $this->getItemByDisplay($nav->get('Top Level'), 'Articles');
        $categories = $this->getItemByDisplay($articles->children(), 'Categories');
        $collections = $this->getItemByDisplay($nav->get('Content'), 'Collections');
        $taxonomies = $this->getItemByDisplay($nav->get('Content'), 'Taxonomies');

        // Ensure old parents are not active
        $this->assertFalse($collections->isActive());
        $this->assertFalse($taxonomies->isActive());

        // Ensure moved item is active
        $this->assertTrue($articles->isActive());

        // Child should not be active in this case though
        $this->assertFalse($categories->isActive());
    }

    #[Test]
    public function active_nav_check_still_functions_properly_on_explicit_child_within_moved_items()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/taxonomies/categories')
            ->assertStatus(200);

        $nav = $this->build([
            'top_level' => [
                'content::collections::articles' => [
                    'action' => '@move',
                    'children' => [
                        'content::taxonomies::categories' => '@move',
                    ],
                ],
            ],
        ]);

        $articles = $this->getItemByDisplay($nav->get('Top Level'), 'Articles');
        $categories = $this->getItemByDisplay($articles->children(), 'Categories');
        $collections = $this->getItemByDisplay($nav->get('Content'), 'Collections');
        $taxonomies = $this->getItemByDisplay($nav->get('Content'), 'Taxonomies');

        // Ensure old parents are not active
        $this->assertFalse($collections->isActive());
        $this->assertFalse($taxonomies->isActive());

        // Ensure moved item is active
        $this->assertTrue($articles->isActive());

        // Ensure child of moved item is now active
        $this->assertTrue($categories->isActive());
    }

    #[Test]
    public function active_nav_check_still_functions_properly_on_descendant_of_moved_items()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/collections/articles/entries/create/en')
            ->assertStatus(200);

        $nav = $this->build([
            'top_level' => [
                'content::collections::articles' => [
                    'action' => '@move',
                    'children' => [
                        'content::taxonomies::categories' => '@move',
                    ],
                ],
            ],
        ]);

        $articles = $this->getItemByDisplay($nav->get('Top Level'), 'Articles');
        $categories = $this->getItemByDisplay($articles->children(), 'Categories');
        $collections = $this->getItemByDisplay($nav->get('Content'), 'Collections');
        $taxonomies = $this->getItemByDisplay($nav->get('Content'), 'Taxonomies');

        // Ensure old parents are not active
        $this->assertFalse($collections->isActive());
        $this->assertFalse($taxonomies->isActive());

        // Ensure moved item is active, due to URL hierarchy of current URL being a descendant
        $this->assertTrue($articles->isActive());

        // Child should not be active in this case though
        $this->assertFalse($categories->isActive());
    }

    #[Test]
    public function active_nav_check_still_functions_properly_on_descendant_of_child_within_moved_item()
    {
        Facades\Collection::make('pages')->title('Pages')->save();
        Facades\Collection::make('articles')->title('Articles')->save();

        Facades\Taxonomy::make('tags')->title('Tags')->save();
        Facades\Taxonomy::make('categories')->title('Categories')->save();

        $this
            ->prepareNavCaches()
            ->get('http://localhost/cp/taxonomies/categories/terms/create/en')
            ->assertStatus(200);

        $nav = $this->build([
            'top_level' => [
                'content::collections::articles' => [
                    'action' => '@move',
                    'children' => [
                        'content::taxonomies::categories' => '@move',
                    ],
                ],
            ],
        ]);

        $articles = $this->getItemByDisplay($nav->get('Top Level'), 'Articles');
        $categories = $this->getItemByDisplay($articles->children(), 'Categories');
        $collections = $this->getItemByDisplay($nav->get('Content'), 'Collections');
        $taxonomies = $this->getItemByDisplay($nav->get('Content'), 'Taxonomies');

        // Ensure old parents are not active
        $this->assertFalse($collections->isActive());
        $this->assertFalse($taxonomies->isActive());

        // Ensure moved item is active
        $this->assertTrue($articles->isActive());

        // Child should not be active in this case, due to URL hierarchy of current URL being a descendant
        $this->assertTrue($categories->isActive());
    }

    protected function prepareNavCaches()
    {
        // Clear caches
        Nav::clearCachedUrls();
        $this->assertFalse(Cache::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertFalse(Blink::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertFalse(Cache::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertFalse(Blink::has(NavBuilder::ALL_URLS_CACHE_KEY));

        // Ensure the nav is built and cached so that tests can check `isActive()` on children in unresolved closures
        Nav::build();
        $this->assertTrue(Cache::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertTrue(Blink::has(NavBuilder::UNRESOLVED_CHILDREN_URLS_CACHE_KEY));
        $this->assertTrue(Cache::has(NavBuilder::ALL_URLS_CACHE_KEY));
        $this->assertTrue(Blink::has(NavBuilder::ALL_URLS_CACHE_KEY));

        return $this;
    }

    protected function build($preferences = null)
    {
        return Nav::build($preferences)->pluck('items', 'display');
    }

    protected function buildAndGetItem($sectionDisplay, $itemDisplay)
    {
        $sectionItems = $this->build()->get($sectionDisplay);

        return $this->getItemByDisplay($sectionItems, $itemDisplay);
    }

    protected function getItemByDisplay($items, $display)
    {
        return $items->keyBy->display()->get($display);
    }
}
