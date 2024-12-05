<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class NavsTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['navs'];

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'navs')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'navs')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{navs}'])
            ->assertSee('Cannot query field \"navs\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_navs()
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

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'navs')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'navs')->andReturn(Nav::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['navs' => [
                ['handle' => 'links', 'title' => 'Links'],
                ['handle' => 'footer', 'title' => 'Footer'],
            ]]]);
    }

    #[Test]
    public function it_queries_only_allowed_sub_resources()
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

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'navs')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'navs')->andReturn(['footer'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['navs' => [
                ['handle' => 'footer', 'title' => 'Footer'],
            ]]]);
    }
}
