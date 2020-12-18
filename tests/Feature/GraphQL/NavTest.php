<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class NavTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_a_nav_by_handle()
    {
        config(['app.debug' => true]);
        Nav::make('links')->title('Links')->maxDepth(1)->expectsRoot(false)->tap(function ($nav) {
            $nav->addTree($nav->makeTree('en'));
            $nav->save();
        });
        Nav::make('footer')->title('Footer')->maxDepth(1)->expectsRoot(false)->tap(function ($nav) {
            $nav->addTree($nav->makeTree('en'));
            $nav->save();
        });

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'nav' => [
                    'handle' => 'footer',
                    'title' => 'Footer',
                ],
            ]]);
    }
}
