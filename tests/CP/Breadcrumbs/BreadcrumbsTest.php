<?php

namespace CP\Breadcrumbs;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\SmarterBreadcrumbs;
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

        $breadcrumbs = SmarterBreadcrumbs::build();

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

        $breadcrumbs = SmarterBreadcrumbs::build();

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

        $breadcrumbs = SmarterBreadcrumbs::build();

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

        $breadcrumbs = SmarterBreadcrumbs::build();

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

        $breadcrumbs = SmarterBreadcrumbs::build();

        $this->assertEquals([], $breadcrumbs);
    }
}
