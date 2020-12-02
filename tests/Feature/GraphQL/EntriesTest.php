<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Standard Blog Post',
            'intro' => 'The intro',
            'content' => 'The standard blog post content',
        ])->create();

        EntryFactory::collection('blog')->id('2')->data([
            'blueprint' => 'art_directed',
            'title' => 'Art Directed Blog Post',
            'hero_image' => 'hero.jpg',
            'content' => 'The art directed blog post content',
        ])->create();

        EntryFactory::collection('events')->id('3')->data(['title' => 'Event One'])->create();

        EntryFactory::collection('events')->id('4')->data(['title' => 'Event Two'])->create();

        EntryFactory::collection('food')->id('5')->data([
            'title' => 'Hamburger',
            'calories' => 350,
        ])->create();

        $article = Blueprint::makeFromFields([
            'intro' => ['type' => 'text'],
            'content' => ['type' => 'textarea'],
        ]);
        $artDirected = Blueprint::makeFromFields([
            'hero_image' => ['type' => 'text'],
            'content' => ['type' => 'textarea'],
        ]);
        $event = Blueprint::makeFromFields([]);
        $food = Blueprint::makeFromFields([
            'calories' => ['type' => 'integer'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
            'art_directed' => $artDirected->setHandle('art_directed'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/events')->andReturn(collect([
            'event' => $event->setHandle('event'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/food')->andReturn(collect([
            'food' => $food->setHandle('food'),
        ]));
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
