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

    /** @test */
    public function it_queries_entries_from_a_single_collection()
    {
        EntryFactory::collection('blog')->id('1')->data(['title' => 'First'])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Second'])->create();
        EntryFactory::collection('events')->id('3')->data(['title' => 'Third'])->create();
        EntryFactory::collection('events')->id('4')->data(['title' => 'Fourth'])->create();
        EntryFactory::collection('articles')->id('5')->data(['title' => 'Fifth'])->create();
        $this->assertCount(5, Entry::all());

        $query = <<<'GQL'
{
    entries(collection: "events") {
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
                ['id' => '3', 'title' => 'Third'],
                ['id' => '4', 'title' => 'Fourth'],
            ]]);
    }

    /** @test */
    public function it_queries_entries_from_multiple_collections()
    {
        EntryFactory::collection('blog')->id('1')->data(['title' => 'First'])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Second'])->create();
        EntryFactory::collection('events')->id('3')->data(['title' => 'Third'])->create();
        EntryFactory::collection('events')->id('4')->data(['title' => 'Fourth'])->create();
        EntryFactory::collection('articles')->id('5')->data(['title' => 'Fifth'])->create();
        $this->assertCount(5, Entry::all());

        $query = <<<'GQL'
{
    entries(collection: ["blog", "articles"]) {
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
                ['id' => '5', 'title' => 'Fifth'],
            ]]);
    }
}
