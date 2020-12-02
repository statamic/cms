<?php

namespace Tests\Feature\GraphQL;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;

/** @group graphql */
class EntriesTest extends GraphQLTestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_entries()
    {
        EntryFactory::collection('blog')->id('1')->data(['title' => 'First'])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Second'])->create();
        $this->assertCount(2, Entry::all());

        $query = <<<'GQL'
{
    entries {
        id
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertOk()
            ->assertGqlData(['entries' => [
                ['id' => '1', 'title' => 'First'],
                ['id' => '2', 'title' => 'Second'],
            ]]);
    }
}
