<?php

namespace Tests\CP\Navigation;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
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

    /** @test */
    public function it_renders_core_children_closure_when_not_active()
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_properly_handles_various_descendant_edge_cases_when_checking_is_active_on_nav_children()
    {
        // Ensure urls are not cached so that we can test regex based isActive() checks
        Nav::clearCachedUrls();

        // These patterns are only intended to check against descendants of child items, since we have explicit child URLs.
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

    protected function build()
    {
        return Nav::build()->pluck('items', 'display');
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
