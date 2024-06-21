<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TaxonomyTest extends TestCase
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

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{taxonomy}'])
            ->assertSee('Cannot query field \"taxonomy\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_a_taxonomy_by_handle()
    {
        $query = <<<'GQL'
{
    taxonomy(handle: "categories") {
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
            ->assertExactJson(['data' => [
                'taxonomy' => [
                    'handle' => 'categories',
                    'title' => 'Categories',
                ],
            ]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        $query = <<<'GQL'
{
    taxonomy(handle: "categories") {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn([])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'handle' => ['Forbidden: categories'],
                        ],
                    ],
                ]],
                'data' => [
                    'taxonomy' => null,
                ],
            ]);
    }
}
