<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class TaxonomiesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_taxonomies()
    {
        Taxonomy::make('tags')->title('Tags')->save();
        Taxonomy::make('categories')->title('Categories')->save();

        $query = <<<'GQL'
{
    taxonomies {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['taxonomies' => [
                ['handle' => 'tags', 'title' => 'Tags'],
                ['handle' => 'categories', 'title' => 'Categories'],
            ]]]);
    }
}
