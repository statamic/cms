<?php

namespace CP\Breadcrumbs;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    #[Test]
    public function it_builds_breadcrumbs_correctly_for_top_level_page()
    {
        $this->actingAs(User::make()->makeSuper())->get(cp_route('dashboard'));

        $breadcrumbs = Breadcrumbs::build();

        $this->assertEquals([
            new Breadcrumb(
                text: 'Dashboard',
                url: 'http://localhost/cp/dashboard',
                icon: 'dashboard',
                links: []
            ),
        ], $breadcrumbs);
    }

    #[Test]
    public function it_builds_breadcrumbs_correctly_for_content_index_page()
    {
        $this->actingAs(User::make()->makeSuper())->get(cp_route('collections.index'));

        $breadcrumbs = Breadcrumbs::build();

        $this->assertEquals([
            new Breadcrumb(
                text: 'Collections',
                url: 'http://localhost/cp/collections',
                icon: 'collections',
                links: [
                    ['icon' => 'navigation', 'text' => 'Navigation', 'url' => 'http://localhost/cp/navigation'],
                    ['icon' => 'taxonomies', 'text' => 'Taxonomies', 'url' => 'http://localhost/cp/taxonomies'],
                    ['icon' => 'assets', 'text' => 'Assets', 'url' => 'http://localhost/cp/assets'],
                    ['icon' => 'globals', 'text' => 'Globals', 'url' => 'http://localhost/cp/globals'],
                ]
            ),
        ], $breadcrumbs);
    }

    #[Test]
    public function it_builds_breadcrumbs_correctly_for_content_show_page()
    {
        Collection::make('pages')->title('Pages')->save();
        Collection::make('articles')->title('Articles')->save();
        Collection::make('case_studies')->title('Case Studies')->icon('book-next-page')->save();

        $this->actingAs(User::make()->makeSuper())->get(cp_route('collections.show', 'pages'));

        $breadcrumbs = Breadcrumbs::build();

        $this->assertEquals([
            new Breadcrumb(
                text: 'Collections',
                url: 'http://localhost/cp/collections',
                icon: 'collections',
                links: [
                    ['icon' => 'navigation', 'text' => 'Navigation', 'url' => 'http://localhost/cp/navigation'],
                    ['icon' => 'taxonomies', 'text' => 'Taxonomies', 'url' => 'http://localhost/cp/taxonomies'],
                    ['icon' => 'assets', 'text' => 'Assets', 'url' => 'http://localhost/cp/assets'],
                    ['icon' => 'globals', 'text' => 'Globals', 'url' => 'http://localhost/cp/globals'],
                ]
            ),
            new Breadcrumb(
                text: 'Pages',
                url: 'http://localhost/cp/collections/pages',
                icon: 'collections',
                links: [
                    ['icon' => 'collections', 'text' => 'Articles', 'url' => 'http://localhost/cp/collections/articles'],
                    ['icon' => 'book-next-page', 'text' => 'Case Studies', 'url' => 'http://localhost/cp/collections/case_studies'],
                ],
                createLabel: 'Create Collection',
                createUrl: 'http://localhost/cp/collections/create',
                configureUrl: 'http://localhost/cp/collections/pages/edit'
            ),
        ], $breadcrumbs);
    }

    #[Test]
    public function it_ignores_nav_preferences_when_building_breadcrumbs()
    {
        $this->actingAs($user = User::make()->makeSuper())->get(cp_route('dashboard'));

        $user->preferences([
            'nav' => [
                'content' => [
                    'reorder' => [
                        'top_level::dashboard',
                    ],
                    'items' => [
                        'top_level::dashboard' => [
                            'action' => '@move',
                            'display' => 'The Dashboard',
                            'icon' => 'browser-com',
                        ],
                    ],
                ],
            ],
        ]);

        $breadcrumbs = Breadcrumbs::build();

        // None of the preferences should be applied to the breadcrumbs.
        $this->assertEquals([
            new Breadcrumb(
                text: 'Dashboard',
                url: 'http://localhost/cp/dashboard',
                icon: 'dashboard',
                links: []
            ),
        ], $breadcrumbs);
    }

    #[Test]
    public function it_doesnt_build_breadcrumbs_for_pages_not_in_the_nav()
    {
        $this->actingAs(User::make()->makeSuper())->get(cp_route('playground'));

        $breadcrumbs = Breadcrumbs::build();

        $this->assertEquals([], $breadcrumbs);
    }

    #[Test]
    public function it_can_push_additional_breadcrumbs()
    {
        $this->actingAs(User::make()->makeSuper())->get(cp_route('dashboard'));

        Breadcrumbs::push(new Breadcrumb(
            text: 'Custom One',
            url: 'http://localhost/cp/custom/one',
            icon: 'icon-one',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: 'Custom Two',
            url: 'http://localhost/cp/custom/two',
            icon: 'icon-two',
        ));

        $breadcrumbs = Breadcrumbs::build();

        $this->assertCount(3, $breadcrumbs);

        $this->assertEquals('Dashboard', $breadcrumbs[0]->text());

        $this->assertEquals('Custom One', $breadcrumbs[1]->text());
        $this->assertEquals('http://localhost/cp/custom/one', $breadcrumbs[1]->url());
        $this->assertEquals('icon-one', $breadcrumbs[1]->icon());

        $this->assertEquals('Custom Two', $breadcrumbs[2]->text());
        $this->assertEquals('http://localhost/cp/custom/two', $breadcrumbs[2]->url());
        $this->assertEquals('icon-two', $breadcrumbs[2]->icon());
    }

    #[Test]
    public function it_can_push_additional_breadcrumb_without_url()
    {
        $this->actingAs(User::make()->makeSuper())->get(cp_route('dashboard'));

        Breadcrumbs::push(new Breadcrumb(
            text: 'Custom',
            icon: 'custom-icon',
        ));

        $breadcrumbs = Breadcrumbs::build();

        $this->assertCount(2, $breadcrumbs);

        $this->assertEquals('Dashboard', $breadcrumbs[0]->text());

        $this->assertEquals('Custom', $breadcrumbs[1]->text());
        $this->assertNull($breadcrumbs[1]->url());
        $this->assertEquals('custom-icon', $breadcrumbs[1]->icon());
    }
}
