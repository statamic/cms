<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class TaxonomiesTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['taxonomies'];

    public function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->title('Tags')->save();
        Taxonomy::make('categories')->title('Categories')->save();
    }

    /** @test */
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{taxonomies}'])
            ->assertSee('Cannot query field \"taxonomies\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_taxonomies()
    {
        $query = <<<'GQL'
{
    taxonomies {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn(Taxonomy::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['taxonomies' => [
                ['handle' => 'tags', 'title' => 'Tags'],
                ['handle' => 'categories', 'title' => 'Categories'],
            ]]]);
    }

    /** @test */
    public function it_queries_only_allowed_sub_resources()
    {
        $query = <<<'GQL'
{
    taxonomies {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn(['categories'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['taxonomies' => [
                ['handle' => 'categories', 'title' => 'Categories'],
            ]]]);
    }
}
