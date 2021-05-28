<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use CreatesQueryableTestEntries;
    use EnablesQueries;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    /**
     * @test
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{entries}'])
            ->assertSee('Cannot query field \"entries\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_entries()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '4', 'title' => 'Event Two'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]]);
    }

    /** @test */
    public function it_paginates_entries()
    {
        $this->createEntries();
        // Add some more entries to be able to make pagination assertions a little more obvious
        EntryFactory::collection('food')->id('6')->slug('cheeseburger')->data(['title' => 'Cheeseburger'])->create();
        EntryFactory::collection('food')->id('7')->slug('fries')->data(['title' => 'Fries'])->create();

        $query = <<<'GQL'
{
    entries(limit: 2, page: 3) {
        total
        per_page
        current_page
        from
        to
        last_page
        has_more_pages
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => [
                'total' => 7,
                'per_page' => 2,
                'current_page' => 3,
                'from' => 5,
                'to' => 6,
                'last_page' => 4,
                'has_more_pages' => true,
                'data' => [
                    ['id' => '5', 'title' => 'Hamburger'],
                    ['id' => '6', 'title' => 'Cheeseburger'],
                ],
            ]]]);
    }

    /** @test */
    public function it_queries_entries_from_a_single_collection()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries(collection: "events") {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '4', 'title' => 'Event Two'],
            ]]]]);
    }

    /** @test */
    public function it_queries_entries_from_multiple_collections()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries(collection: ["blog", "food"]) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]]);
    }

    /** @test */
    public function it_queries_entries_from_multiple_collections_using_variables()
    {
        $this->createEntries();

        $query = <<<'GQL'
query($collection:[String]) {
    entries(collection: $collection) {
        data {
            id
            title
        }
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
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]]);
    }

    /** @test */
    public function it_queries_blueprint_specific_fields()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries(collection: ["blog", "food"]) {
        data {
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
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
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
            ]]]]);
    }

    /** @test */
    public function it_filters_entries()
    {
        $this->createEntries();

        EntryFactory::collection('blog')
            ->id('6')
            ->slug('that-was-so-rad')
            ->data(['title' => 'That was so rad!'])
            ->create();

        EntryFactory::collection('blog')
            ->id('7')
            ->slug('as-cool-as-radcliffe')
            ->data(['title' => 'I wish I was as cool as Daniel Radcliffe!'])
            ->create();

        EntryFactory::collection('blog')
            ->id('8')
            ->slug('i-hate-radishes')
            ->data(['title' => 'I hate radishes.'])
            ->create();

        $query = <<<'GQL'
{
    entries(filter: {
        title: {
            contains: "rad",
            ends_with: "!"
        }
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                [
                    'id' => '6',
                    'title' => 'That was so rad!',
                ],
                [
                    'id' => '7',
                    'title' => 'I wish I was as cool as Daniel Radcliffe!',
                ],
            ]]]]);
    }

    /** @test */
    public function it_filters_entries_with_equalto_shorthand()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries(filter: {
        title: "Hamburger"
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                [
                    'id' => '5',
                    'title' => 'Hamburger',
                ],
            ]]]]);
    }

    /** @test */
    public function it_filters_entries_with_multiple_conditions_of_the_same_type()
    {
        $this->createEntries();

        EntryFactory::collection('blog')
            ->id('6')
            ->slug('this-is-rad')
            ->data(['title' => 'This is rad'])
            ->create();

        EntryFactory::collection('blog')
            ->id('7')
            ->slug('this-is-awesome')
            ->data(['title' => 'This is awesome'])
            ->create();

        EntryFactory::collection('blog')
            ->id('8')
            ->slug('this-is-rad-and-awesome')
            ->data(['title' => 'This is both rad and awesome'])
            ->create();

        $query = <<<'GQL'
{
    entries(filter: {
        title: [
            { contains: "rad" },
            { contains: "awesome" },
        ]
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                [
                    'id' => '8',
                    'title' => 'This is both rad and awesome',
                ],
            ]]]]);
    }

    /** @test */
    public function it_sorts_entries()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries(sort: "title") {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '4', 'title' => 'Event Two'],
                ['id' => '5', 'title' => 'Hamburger'],
                ['id' => '1', 'title' => 'Standard Blog Post'],
            ]]]]);
    }

    /** @test */
    public function it_sorts_entries_descending()
    {
        $this->createEntries();

        $query = <<<'GQL'
{
    entries(sort: "title desc") {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '5', 'title' => 'Hamburger'],
                ['id' => '4', 'title' => 'Event Two'],
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '2', 'title' => 'Art Directed Blog Post'],
            ]]]]);
    }

    /** @test */
    public function it_sorts_entries_on_multiple_fields()
    {
        $blueprint = Blueprint::makeFromFields([
            'number' => ['type' => 'integer'],
        ])->setHandle('test');

        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect(['test' => $blueprint]));

        EntryFactory::collection('test')->id('1')->slug('1')->data(['title' => 'Beta', 'number' => 2])->create();
        EntryFactory::collection('test')->id('2')->slug('2')->data(['title' => 'Alpha', 'number' => 2])->create();
        EntryFactory::collection('test')->id('3')->slug('3')->data(['title' => 'Alpha', 'number' => 1])->create();
        EntryFactory::collection('test')->id('4')->slug('4')->data(['title' => 'Beta', 'number' => 1])->create();

        $query = <<<'GQL'
{
    entries(collection: "test", sort: ["title", "number desc"]) {
        data {
            id
            title
            ... on Entry_Test_Test {
                number
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '2', 'title' => 'Alpha', 'number' => 2],
                ['id' => '3', 'title' => 'Alpha', 'number' => 1],
                ['id' => '1', 'title' => 'Beta', 'number' => 2],
                ['id' => '4', 'title' => 'Beta', 'number' => 1],
            ]]]]);
    }

    /** @test */
    public function it_only_shows_published_entries_by_default()
    {
        $this->createEntries();
        Entry::find(1)->date(now()->addMonths(2))->save();
        Entry::find(2)->published(false)->save();
        Entry::find(4)->published(false)->save();

        $query = <<<'GQL'
{
    entries {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]]);

        $query = <<<'GQL'
{
    entries(filter: {published: true}) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['entries' => ['data' => [
                ['id' => '1', 'title' => 'Standard Blog Post'],
                ['id' => '3', 'title' => 'Event One'],
                ['id' => '5', 'title' => 'Hamburger'],
            ]]]]);

        $query = <<<'GQL'
{
    entries(filter: {status: "draft"}) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
                ->withoutExceptionHandling()
                ->post('/graphql', ['query' => $query])
                ->assertGqlOk()
                ->assertExactJson(['data' => ['entries' => ['data' => [
                    ['id' => '2', 'title' => 'Art Directed Blog Post'],
                    ['id' => '4', 'title' => 'Event Two'],
                ]]]]);

        $query = <<<'GQL'
{
    entries(filter: {published: false}) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
                ->withoutExceptionHandling()
                ->post('/graphql', ['query' => $query])
                ->assertGqlOk()
                ->assertExactJson(['data' => ['entries' => ['data' => [
                    ['id' => '2', 'title' => 'Art Directed Blog Post'],
                    ['id' => '4', 'title' => 'Event Two'],
                ]]]]);

        $query = <<<'GQL'
{
    entries(filter: {status: "scheduled"}) {
        data {
            id
            title
        }
    }
}
GQL;

        $this
                ->withoutExceptionHandling()
                ->post('/graphql', ['query' => $query])
                ->assertGqlOk()
                ->assertExactJson(['data' => ['entries' => ['data' => [
                    ['id' => '1', 'title' => 'Standard Blog Post'],
                ]]]]);
    }
}
