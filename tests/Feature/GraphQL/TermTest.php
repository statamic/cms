<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TermTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['taxonomies'];

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{terms}'])
            ->assertSee('Cannot query field \"terms\" on type \"Query\"', false);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('charlie')->data(['title' => 'Charlie'])->save();

        $query = <<<'GQL'
{
    term(id: "tags::bravo") {
        id
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn([])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'id' => ['Forbidden: tags'],
                        ],
                    ],
                ]],
                'data' => [
                    'term' => null,
                ],
            ]);
    }

    #[Test]
    public function it_queries_a_term_by_id()
    {
        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('charlie')->data(['title' => 'Charlie'])->save();

        $query = <<<'GQL'
{
    term(id: "tags::bravo") {
        id
        title
        slug
        url
        uri
        edit_url
        permalink
        taxonomy {
            title
            handle
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn(Taxonomy::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'term' => [
                    'id' => 'tags::bravo',
                    'title' => 'Bravo',
                    'slug' => 'bravo',
                    'url' => '/tags/bravo',
                    'uri' => '/tags/bravo',
                    'edit_url' => 'http://localhost/cp/taxonomies/tags/terms/bravo/en',
                    'permalink' => 'http://localhost/tags/bravo',
                    'taxonomy' => [
                        'title' => 'Tags',
                        'handle' => 'tags',
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_can_add_custom_fields_to_interface()
    {
        GraphQL::addField('TermInterface', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('TermInterface', 'two', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        GraphQL::addField('TermInterface', 'title', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden title';
                },
            ];
        });

        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();

        $query = <<<'GQL'
{
    term(id: "tags::alpha") {
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
                'term' => [
                    'id' => 'tags::alpha',
                    'one' => 'first',
                    'two' => 'second',
                    'title' => 'the overridden title',
                ],
            ]]);
    }

    #[Test]
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('Term_Tags_Tag', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('Term_Tags_Tag', 'title', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden title';
                },
            ];
        });

        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();

        $query = <<<'GQL'
{
    term(id: "tags::alpha") {
        id
        ... on Term_Tags_Tag {
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
                'term' => [
                    'id' => 'tags::alpha',
                    'one' => 'first',
                    'title' => 'the overridden title',
                ],
            ]]);
    }

    #[Test]
    public function adding_custom_field_to_an_implementation_does_not_add_it_to_the_interface()
    {
        GraphQL::addField('Term_Tags_Tag', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();

        $query = <<<'GQL'
{
    term(id: "tags::alpha") {
        id
        one
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson(['errors' => [[
                'message' => 'Cannot query field "one" on type "TermInterface". Did you mean to use an inline fragment on "Term_Tags_Tag"?',
            ]]]);
    }

    #[Test]
    public function it_resolves_query_builders()
    {
        BlueprintRepository::partialMock();

        $blueprint = Blueprint::makeFromFields([])->setHandle('test');
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect(['test' => $blueprint]));
        EntryFactory::collection('test')->id('bravo')->data(['title' => 'Bravo'])->create();
        EntryFactory::collection('test')->id('charlie')->data(['title' => 'Charlie'])->create();

        $blueprint = Blueprint::makeFromFields(['entries_field' => ['type' => 'entries']])->setHandle('tags');
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tags' => $blueprint]));

        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data([
            'title' => 'Alpha',
            'entries_field' => ['bravo', 'charlie'],
        ])->save();

        $query = <<<'GQL'
{
    term(id: "tags::alpha") {
        id
        ... on Term_Tags_Tags {
            entries_field {
                id
                title
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'term' => [
                    'id' => 'tags::alpha',
                    'entries_field' => [
                        [
                            'id' => 'bravo',
                            'title' => 'Bravo',
                        ],
                        [
                            'id' => 'charlie',
                            'title' => 'Charlie',
                        ],
                    ],
                ],
            ]]);
    }
}
