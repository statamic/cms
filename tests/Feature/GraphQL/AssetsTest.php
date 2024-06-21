<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class AssetsTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['assets'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{assets}'])
            ->assertSee('Cannot query field \"assets\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_assets()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "test") {
        data {
            path
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['test'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'a.txt'],
                ['path' => 'b.txt'],
            ]]]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        AssetContainer::make('one')->disk('test')->save();
        AssetContainer::make('two')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "two") {
        data {
            path
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['one'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'container' => ['Forbidden: two'],
                        ],
                    ],
                ]],
                'data' => [
                    'assets' => null,
                ],
            ]);
    }

    #[Test]
    public function it_paginates_assets()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        Storage::disk('test')->put('d.txt', '');
        Storage::disk('test')->put('e.txt', '');
        Storage::disk('test')->put('f.txt', '');
        Storage::disk('test')->put('g.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "test" limit: 2, page: 3) {
        total
        per_page
        current_page
        from
        to
        last_page
        has_more_pages
        data {
            path
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => [
                'total' => 7,
                'per_page' => 2,
                'current_page' => 3,
                'from' => 5,
                'to' => 6,
                'last_page' => 4,
                'has_more_pages' => true,
                'data' => [
                    ['path' => 'e.txt'],
                    ['path' => 'f.txt'],
                ],
            ]]]);
    }

    #[Test]
    public function it_queries_blueprint_specific_fields()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('.meta/a.txt.yaml', YAML::dump([
            'data' => [
                'alt' => 'the a file',
                'foo' => 'bar',
            ],
        ]));
        Storage::disk('test')->put('.meta/b.txt.yaml', YAML::dump([
            'data' => [
                'alt' => 'the b file',
            ],
        ]));

        $blueprint = Blueprint::makeFromFields([
            'alt' => ['type' => 'text'],
            'foo' => ['type' => 'text'],
        ])->setHandle('test');

        BlueprintRepository::shouldReceive('find')->with('assets/test')->andReturn($blueprint);

        $query = <<<'GQL'
{
    assets(container: "test") {
        data {
            path
            ... on Asset_Test {
                alt
                foo
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                [
                    'path' => 'a.txt',
                    'alt' => 'the a file',
                    'foo' => 'bar',
                ],
                [
                    'path' => 'b.txt',
                    'alt' => 'the b file',
                    'foo' => null,
                ],
            ]]]]);
    }

    #[Test]
    public function it_cannot_filter_assets_by_default()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('not-image.txt', '');
        Storage::disk('test')->put('image.jpg', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "test", filter: {
        is_image: {
            is: true
        }
    }) {
        data {
            path
            is_image
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['test'])->once();
        ResourceAuthorizer::makePartial();

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'assets', 'test')
            ->andReturn([])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'filter' => ['Forbidden: is_image'],
                        ],
                    ],
                ]],
                'data' => [
                    'assets' => null,
                ],
            ]);
    }

    #[Test]
    public function it_can_filter_assets_when_configuration_allows_for_it()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('not-image.txt', '');
        Storage::disk('test')->put('image.jpg', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "test", filter: {
        is_image: {
            is: true
        }
    }) {
        data {
            path
            is_image
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['test'])->once();
        ResourceAuthorizer::makePartial();

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'assets', 'test')
            ->andReturn(['is_image'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'image.jpg', 'is_image' => true],
            ]]]]);
    }

    #[Test]
    public function it_filters_assets_with_equalto_shorthand()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('not-image.txt', '');
        Storage::disk('test')->put('image.jpg', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "test", filter: {
        is_image: true
    }) {
        data {
            path
            is_image
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['test'])->once();
        ResourceAuthorizer::makePartial();

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'assets', 'test')
            ->andReturn(['is_image'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'image.jpg', 'is_image' => true],
            ]]]]);
    }

    #[Test]
    public function it_filters_assets_with_multiple_conditions_of_the_same_type()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('not-image.txt', '');
        Storage::disk('test')->put('favourite-image.jpg', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    assets(container: "test", filter: {
        path: [
            { contains: "favourite" },
            { contains: "image" }
        ]
    }) {
        data {
            path
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['test'])->once();
        ResourceAuthorizer::makePartial();

        FilterAuthorizer::shouldReceive('allowedForSubResources')
            ->with('graphql', 'assets', 'test')
            ->andReturn(['path'])
            ->once();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'favourite-image.jpg'],
            ]]]]);
    }

    #[Test]
    public function it_sorts_assets()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('.meta/a.txt.yaml', YAML::dump(['data' => ['alt' => 'z']]));
        Storage::disk('test')->put('.meta/b.txt.yaml', YAML::dump(['data' => ['alt' => 'a']]));

        $query = <<<'GQL'
{
    assets(container: "test", sort: "alt") {
        data {
            path
            ... on Asset_Test {
                alt
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'b.txt', 'alt' => 'a'],
                ['path' => 'a.txt', 'alt' => 'z'],
            ]]]]);
    }

    #[Test]
    public function it_sorts_assets_descending()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('.meta/a.txt.yaml', YAML::dump(['data' => ['alt' => 'z']]));
        Storage::disk('test')->put('.meta/b.txt.yaml', YAML::dump(['data' => ['alt' => 'a']]));

        $query = <<<'GQL'
{
    assets(container: "test", sort: "alt desc") {
        data {
            path
            ... on Asset_Test {
                alt
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'a.txt', 'alt' => 'z'],
                ['path' => 'b.txt', 'alt' => 'a'],
            ]]]]);
    }

    #[Test]
    public function it_sorts_assets_on_multiple_fields()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('1.txt', '');
        Storage::disk('test')->put('2.txt', '');
        Storage::disk('test')->put('3.txt', '');
        Storage::disk('test')->put('4.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('.meta/1.txt.yaml', YAML::dump(['data' => ['alt' => 'Beta', 'number' => 2]]));
        Storage::disk('test')->put('.meta/2.txt.yaml', YAML::dump(['data' => ['alt' => 'Alpha', 'number' => 2]]));
        Storage::disk('test')->put('.meta/3.txt.yaml', YAML::dump(['data' => ['alt' => 'Alpha', 'number' => 1]]));
        Storage::disk('test')->put('.meta/4.txt.yaml', YAML::dump(['data' => ['alt' => 'Beta', 'number' => 1]]));

        $blueprint = Blueprint::makeFromFields([
            'alt' => ['type' => 'text'],
            'number' => ['type' => 'integer'],
        ])->setHandle('test');

        BlueprintRepository::shouldReceive('find')->with('assets/test')->andReturn($blueprint);

        $query = <<<'GQL'
{
    assets(container: "test", sort: ["alt", "number desc"]) {
        data {
            path
            ... on Asset_Test {
                alt
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
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => '2.txt', 'alt' => 'Alpha', 'number' => 2],
                ['path' => '3.txt', 'alt' => 'Alpha', 'number' => 1],
                ['path' => '1.txt', 'alt' => 'Beta', 'number' => 2],
                ['path' => '4.txt', 'alt' => 'Beta', 'number' => 1],
            ]]]]);
    }
}
