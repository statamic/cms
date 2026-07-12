<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TermsTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['taxonomies'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    private function createTaxonomies()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        Taxonomy::make('sizes')->save();

        return $this;
    }

    private function createTerms()
    {
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Tag Alpha'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Tag Bravo'])->save();
        Term::make()->taxonomy('categories')->inDefaultLocale()->slug('alpha')->data(['title' => 'Category Alpha'])->save();
        Term::make()->taxonomy('categories')->inDefaultLocale()->slug('bravo')->data(['title' => 'Category Bravo'])->save();
        Term::make()->taxonomy('sizes')->inDefaultLocale()->slug('small')->data(['title' => 'Size Small', 'shorthand' => 'sm'])->save();
        Term::make()->taxonomy('sizes')->inDefaultLocale()->slug('large')->data(['title' => 'Size Large', 'shorthand' => 'lg'])->save();

        return $this;
    }

    private function createBlueprints()
    {
        $tag = Blueprint::makeFromFields([]);
        $category = Blueprint::makeFromFields([]);
        $size = Blueprint::makeFromFields(['shorthand' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tag' => $tag->setHandle('tag')]));
        BlueprintRepository::shouldReceive('in')->with('taxonomies/categories')->andReturn(collect(['category' => $category->setHandle('category')]));
        BlueprintRepository::shouldReceive('in')->with('taxonomies/sizes')->andReturn(collect(['size' => $size->setHandle('size')]));

        return $this;
    }

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
    public function it_queries_all_terms()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms {
        data {
            id
            title
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn(Taxonomy::handles()->all())->twice();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::alpha', 'title' => 'Tag Alpha'],
                ['id' => 'tags::bravo', 'title' => 'Tag Bravo'],
                ['id' => 'categories::alpha', 'title' => 'Category Alpha'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
                ['id' => 'sizes::small', 'title' => 'Size Small'],
                ['id' => 'sizes::large', 'title' => 'Size Large'],
            ]]]]);
    }

    #[Test]
    public function it_queries_all_terms_in_a_specific_site()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        Taxonomy::find('tags')->sites(['en', 'fr'])->save();

        Term::find('tags::alpha')->in('fr')->data(['title' => 'Le Alpha'])->save();

        $query = <<<'GQL'
{
    terms(site: "fr") {
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
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::alpha', 'title' => 'Le Alpha'],
            ]]]]);
    }

    #[Test]
    public function it_queries_only_terms_on_allowed_sub_resources()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms {
        data {
            id
            title
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn(['categories'])->twice();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'categories::alpha', 'title' => 'Category Alpha'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(taxonomy: "tags") {
        data {
            id
            title
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'taxonomies')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'taxonomies')->andReturn(['categories'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'taxonomy' => ['Forbidden: tags'],
                        ],
                    ],
                ]],
                'data' => [
                    'terms' => null,
                ],
            ]);
    }

    #[Test]
    public function it_paginates_terms()
    {
        $this->createTaxonomies()->createBlueprints();

        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('charlie')->data(['title' => 'Charlie'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('delta')->data(['title' => 'Delta'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('echo')->data(['title' => 'Echo'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('foxtrot')->data(['title' => 'Foxtrot'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('golf')->data(['title' => 'Golf'])->save();

        $query = <<<'GQL'
{
    terms(limit: 2, page: 3) {
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
            ->assertExactJson(['data' => ['terms' => [
                'total' => 7,
                'per_page' => 2,
                'current_page' => 3,
                'from' => 5,
                'to' => 6,
                'last_page' => 4,
                'has_more_pages' => true,
                'data' => [
                    ['id' => 'tags::echo', 'title' => 'Echo'],
                    ['id' => 'tags::foxtrot', 'title' => 'Foxtrot'],
                ],
            ]]]);
    }

    #[Test]
    public function it_queries_terms_from_a_single_taxonomy()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(taxonomy: "categories") {
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
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'categories::alpha', 'title' => 'Category Alpha'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_queries_terms_from_multiple_taxonomies()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(taxonomy: ["categories", "sizes"]) {
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
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'categories::alpha', 'title' => 'Category Alpha'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
                ['id' => 'sizes::small', 'title' => 'Size Small'],
                ['id' => 'sizes::large', 'title' => 'Size Large'],
            ]]]]);
    }

    #[Test]
    public function it_queries_blueprint_specific_fields()
    {
        $this->createTaxonomies()->createTerms();

        // $this->createBlueprints();

        Term::find('tags::alpha')->blueprint('type_one')->set('foo', 'FOO!')->save();
        Term::find('tags::bravo')->blueprint('type_two')->set('bar', 'BAR!')->save();

        $tagOne = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        $tagTwo = Blueprint::makeFromFields(['bar' => ['type' => 'text']]);
        $category = Blueprint::makeFromFields([]);
        $size = Blueprint::makeFromFields(['bar' => ['type' => 'text']]);
        $size = Blueprint::makeFromFields(['shorthand' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'type_one' => $tagOne->setHandle('type_one'),
            'type_two' => $tagTwo->setHandle('type_two'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('taxonomies/categories')->andReturn(collect(['category' => $category->setHandle('category')]));
        BlueprintRepository::shouldReceive('in')->with('taxonomies/sizes')->andReturn(collect([
            'size' => $size->setHandle('size'),
        ]));

        $query = <<<'GQL'
{
    terms(taxonomy: ["tags", "sizes"]) {
        data {
            id
            ... on Term_Tags_TypeOne {
                foo
            }
            ... on Term_Tags_TypeTwo {
                bar
            }
            ... on Term_Sizes_Size {
                shorthand
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::alpha', 'foo' => 'FOO!'],
                ['id' => 'tags::bravo', 'bar' => 'BAR!'],
                ['id' => 'sizes::small', 'shorthand' => 'sm'],
                ['id' => 'sizes::large', 'shorthand' => 'lg'],
            ]]]]);
    }

    #[Test]
    public function it_cannot_filter_terms_by_default()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(taxonomy: "tags", filter: {
        title: {
            contains: "a",
            ends_with: "o"
        }
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'taxonomies', ['tags'])
            ->andReturn(['tags'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'filter' => ['Forbidden: title'],
                        ],
                    ],
                ]],
                'data' => [
                    'terms' => null,
                ],
            ]);
    }

    #[Test]
    public function it_can_filter_taxonomy_terms_when_configuration_allows_for_it()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(taxonomy: "tags", filter: {
        title: {
            contains: "a",
            ends_with: "o"
        }
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'taxonomies', ['tags'])
            ->andReturn(['title'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::bravo', 'title' => 'Tag Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_can_filter_all_terms_when_configuration_allows_for_it()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(filter: {
        title: {
            contains: "a",
            ends_with: "o"
        }
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'taxonomies', '*')
            ->andReturn(['title'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::bravo', 'title' => 'Tag Bravo'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_filters_terms_with_equalto_shorthand()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(filter: {
        slug: "bravo"
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'taxonomies', '*')
            ->andReturn(['slug'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::bravo', 'title' => 'Tag Bravo'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_filters_terms_with_multiple_conditions_of_the_same_type()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(filter: {
        title: [
            { contains: "Bravo" },
            { contains: "Cat" },
        ]
    }) {
        data {
            id
            title
        }
    }
}
GQL;

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'taxonomies', '*')
            ->andReturn(['title'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_sorts_terms()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(sort: "title") {
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
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'categories::alpha', 'title' => 'Category Alpha'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
                ['id' => 'sizes::large', 'title' => 'Size Large'],
                ['id' => 'sizes::small', 'title' => 'Size Small'],
                ['id' => 'tags::alpha', 'title' => 'Tag Alpha'],
                ['id' => 'tags::bravo', 'title' => 'Tag Bravo'],
            ]]]]);
    }

    #[Test]
    public function it_sorts_terms_descending()
    {
        $this->createTaxonomies()->createTerms()->createBlueprints();

        $query = <<<'GQL'
{
    terms(sort: "title desc") {
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
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::bravo', 'title' => 'Tag Bravo'],
                ['id' => 'tags::alpha', 'title' => 'Tag Alpha'],
                ['id' => 'sizes::small', 'title' => 'Size Small'],
                ['id' => 'sizes::large', 'title' => 'Size Large'],
                ['id' => 'categories::bravo', 'title' => 'Category Bravo'],
                ['id' => 'categories::alpha', 'title' => 'Category Alpha'],
            ]]]]);
    }

    #[Test]
    public function it_sorts_terms_on_multiple_fields()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();

        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('beta')->data(['number' => 2])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['number' => 2])->save();
        Term::make()->taxonomy('categories')->inDefaultLocale()->slug('alpha')->data(['number' => 1])->save();
        Term::make()->taxonomy('categories')->inDefaultLocale()->slug('beta')->data(['number' => 1])->save();

        $tag = Blueprint::makeFromFields(['number' => ['type' => 'integer']])->setHandle('tag');
        $cat = Blueprint::makeFromFields(['number' => ['type' => 'integer']])->setHandle('category');
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['test' => $tag]));
        BlueprintRepository::shouldReceive('in')->with('taxonomies/categories')->andReturn(collect(['category' => $cat]));

        $query = <<<'GQL'
{
    terms(sort: ["slug", "number desc"]) {
        data {
            id
            slug
            ... on Term_Tags_Tag {
                number
            }
            ... on Term_Categories_Category {
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
            ->assertExactJson(['data' => ['terms' => ['data' => [
                ['id' => 'tags::alpha', 'slug' => 'alpha', 'number' => 2],
                ['id' => 'categories::alpha', 'slug' => 'alpha', 'number' => 1],
                ['id' => 'tags::beta', 'slug' => 'beta', 'number' => 2],
                ['id' => 'categories::beta', 'slug' => 'beta', 'number' => 1],
            ]]]]);
    }
}
