<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class NavigationsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['navs'];

    /**
     * @test
     *
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{navs}'])
            ->assertSee('Cannot query field \"navs\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_navigations()
    {
        Nav::make('links')->title('Links')->maxDepth(1)->expectsRoot(false)->tap(function ($nav) {
            $nav->makeTree('en')->save();
            $nav->save();
        });
        Nav::make('footer')->title('Footer')->maxDepth(1)->expectsRoot(false)->tap(function ($nav) {
            $nav->makeTree('en')->save();
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
