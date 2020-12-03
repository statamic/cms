<?php

namespace Tests\Feature\GraphQL;

use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use CreatesQueryableTestEntries;

    public function setUp(): void
    {
        parent::setUp();

        $this->createEntries();
    }

    /** @test */
    public function it_queries_entries()
    {
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
            ->assertExactJson(['data' => ['entries' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '4', 'title' => 'Event Two'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]);
    }

    /** @test */
    public function it_queries_entries_from_a_single_collection()
    {
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
            ->assertExactJson(['data' => ['entries' => [
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '4', 'title' => 'Event Two'],
            ]]]);
    }

    /** @test */
    public function it_queries_entries_from_multiple_collections()
    {
        $query = <<<'GQL'
{
    entries(collection: ["blog", "food"]) {
        id
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['entries' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]);
    }

    /** @test */
    public function it_queries_entries_from_multiple_collections_using_variables()
    {
        $query = <<<'GQL'
query($collection:[String]) {
    entries(collection: $collection) {
        id
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', [
                'query' => $query,
                'variables' => [
                    'collection' => ['blog', 'food'],
                ],
            ])
            ->assertOk()
            ->assertExactJson(['data' => ['entries' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]);
    }

    /** @test */
    public function it_queries_blueprint_specific_fields()
    {
        $query = <<<'GQL'
{
    entries(collection: ["blog", "food"]) {
        id
        title
        ... on Entry_Blog_Article {
            intro
            content
        }
        ... on Entry_Blog_ArtDirected {
            hero_image
            content
        }
        ... on Entry_Food_Food {
            calories
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertOk()
            ->assertExactJson(['data' => ['entries' => [
                [
                    'id' => '1',
                    'title' => 'Standard Blog Post',
                    'intro' => 'The intro',
                    'content' => 'The standard blog post content',
                ],
                [
                    'id' => '2',
                    'title' => 'Art Directed Blog Post',
                    'hero_image' => 'hero.jpg',
                    'content' => 'The art directed blog post content',
                ],
                [
                    'id' => '5',
                    'title' => 'Hamburger',
                    'calories' => 350,
                ],
            ]]]);
    }
}
