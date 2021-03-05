<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class TermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['taxonomies'];

    /**
     * @test
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{terms}'])
            ->assertSee('Cannot query field \"terms\" on type \"Query\"', false);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('Term_Tags_Tags', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('Term_Tags_Tags', 'title', function () {
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
        ... on Term_Tags_Tags {
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

    /** @test */
    public function adding_custom_field_to_an_implementation_does_not_add_it_to_the_interface()
    {
        GraphQL::addField('Term_Tags_Tags', 'one', function () {
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
                'message' => 'Cannot query field "one" on type "TermInterface". Did you mean to use an inline fragment on "Term_Tags_Tags"?',
            ]]]);
    }
}
