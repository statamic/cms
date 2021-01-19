<?php

namespace Tests\Feature\GraphQL;

use GraphQL\Type\Definition\Type;
use Statamic\GraphQL\Types\EntryInterface;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class EntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use CreatesQueryableTestEntries;

    public function setUp(): void
    {
        parent::setUp();

        $this->createEntries();
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
        collection {
            title
            handle
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
                    'collection' => [
                        'title' => 'Events',
                        'handle' => 'events',
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_entry()
    {
        EntryInterface::addField('one', function () {
            return [
                'type' => Type::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        EntryInterface::addField('two', function () {
            return [
                'type' => Type::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        EntryInterface::addField('title', function () {
            return [
                'type' => Type::string(),
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
}
