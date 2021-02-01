<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class NavigationsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_navigations()
    {
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
    navs {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['navs' => [
                ['handle' => 'links', 'title' => 'Links'],
                ['handle' => 'footer', 'title' => 'Footer'],
            ]]]);
    }
}
