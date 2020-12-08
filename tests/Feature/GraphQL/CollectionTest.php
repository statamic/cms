<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_a_collection_by_handle()
    {
        Collection::make('blog')->title('Blog Posts')->save();
        Collection::make('events')->title('Events')->save();

        $query = <<<'GQL'
{
    collection(handle: "events") {
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
                'collection' => [
                    'handle' => 'events',
                    'title' => 'Events',
                ],
            ]]);
    }
}
