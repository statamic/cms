<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class TaxonomyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['taxonomies'];

    /**
     * @test
     *
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{taxonomy}'])
            ->assertSee('Cannot query field \"taxonomy\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_a_taxonomy_by_handle()
    {
        Taxonomy::make('tags')->title('Tags')->save();
        Taxonomy::make('categories')->title('Categories')->save();

        $query = <<<'GQL'
{
    taxonomy(handle: "categories") {
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
                'taxonomy' => [
                    'handle' => 'categories',
                    'title' => 'Categories',
                ],
            ]]);
    }
}
