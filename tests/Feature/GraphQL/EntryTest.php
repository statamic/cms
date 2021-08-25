<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class EntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use CreatesQueryableTestEntries;
    use EnablesQueries;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::partialMock();

        $this->createEntries();
    }

    /**
     * @test
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{entry}'])
            ->assertSee('Cannot query field \"entry\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_an_entry_by_id()
    {
        $query = <<<'GQL'
{
    entry(id: "3") {
        id
        title
        slug
        url
        uri
        edit_url
        permalink
        published
        private
        status
        date
        last_modified
        collection {
            title
            handle
        }
        locale
        site {
            handle
            name
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '3',
                    'title' => 'Event One',
                    'slug' => 'event-one',
                    'url' => '/events/event-one',
                    'uri' => '/events/event-one',
                    'edit_url' => 'http://localhost/cp/collections/events/entries/3/event-one',
                    'permalink' => 'http://localhost/events/event-one',
                    'published' => true,
                    'private' => false,
                    'status' => 'published',
                    'date' => 'November 3rd, 2017',
                    'last_modified' => 'December 25th, 2017',
                    'collection' => [
                        'title' => 'Events',
                        'handle' => 'events',
                    ],
                    'locale' => 'en',
                    'site' => [
                        'handle' => 'en',
                        'name' => 'English',
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_entry_by_slug()
    {
        EntryFactory::collection('blog')->id('123')->slug('foo')->create();
        EntryFactory::collection('events')->id('456')->slug('foo')->create();

        $query = <<<'GQL'
{
    entry(slug: "foo") {
        id
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '123',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_entry_by_slug_and_collection()
    {
        EntryFactory::collection('blog')->id('123')->slug('foo')->create();
        EntryFactory::collection('events')->id('456')->slug('foo')->create();

        $query = <<<'GQL'
{
    entry(slug: "foo", collection: "events") {
        id
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '456',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_entry_by_uri()
    {
        $query = <<<'GQL'
{
    entry(uri: "/events/event-two") {
        id
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '4',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_entry_in_a_specific_site()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        Collection::find('events')->routes('/events/{slug}')->sites(['en', 'fr'])->save();

        EntryFactory::collection('events')->locale('fr')->origin('4')->id('44')->slug('event-two')->create();

        $query = <<<'GQL'
{
    entry(uri: "/events/event-two", site: "fr") {
        id
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '44',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_existing_entry_parent()
    {
        $this->createStructuredCollection();

        $query = <<<'GQL'
{
    entry(id: "4") {
        parent {
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'parent' => [
                        'title' => 'Event One',
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_queries_a_non_existing_entry_parent()
    {
        $this->createStructuredCollection();

        $query = <<<'GQL'
{
    entry(id: "3") {
        parent {
            id
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'parent' => null,
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_interface()
    {
        GraphQL::addField('EntryInterface', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('EntryInterface', 'two', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        GraphQL::addField('EntryInterface', 'title', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden title';
                },
            ];
        });

        $query = <<<'GQL'
{
    entry(id: "3") {
        id
        one
        two
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '3',
                    'one' => 'first',
                    'two' => 'second',
                    'title' => 'the overridden title',
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('Entry_Blog_ArtDirected', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('Entry_Blog_ArtDirected', 'title', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden title';
                },
            ];
        });

        $query = <<<'GQL'
{
    entry(id: "2") {
        id
        ... on Entry_Blog_ArtDirected {
            one
            title
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'id' => '2',
                    'one' => 'first',
                    'title' => 'the overridden title',
                ],
            ]]);
    }

    /** @test */
    public function adding_custom_field_to_an_implementation_does_not_add_it_to_the_interface()
    {
        GraphQL::addField('Entry_Blog_ArtDirected', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        $query = <<<'GQL'
{
    entry(id: "2") {
        id
        one
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson(['errors' => [[
                'message' => 'Cannot query field "one" on type "EntryInterface". Did you mean to use an inline fragment on "Entry_Blog_ArtDirected"?',
            ]]]);
    }

    private function createStructuredCollection()
    {
        $collection = Collection::find('events');
        $structure = (new CollectionStructure)->maxDepth(3);
        $collection->structure($structure)->save();

        $collection->structure()->in('en')->tree([
            ['entry' => '3', 'children' => [
                ['entry' => '4'],
            ]],
        ])->save();
    }
}
