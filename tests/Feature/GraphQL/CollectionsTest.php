<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class CollectionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_collections()
    {
        Collection::make('blog')->title('Blog Posts')->save();
        Collection::make('events')->title('Events')->save();

        $query = <<<'GQL'
{
    collections {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['collections' => [
                ['handle' => 'blog', 'title' => 'Blog Posts'],
                ['handle' => 'events', 'title' => 'Events'],
            ]]]);
    }
}
