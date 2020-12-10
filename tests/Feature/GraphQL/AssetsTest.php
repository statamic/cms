<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class AssetsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_assets()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
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

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assets' => ['data' => [
                ['path' => 'a.txt'],
                ['path' => 'b.txt'],
            ]]]]);
    }

    /** @test */
    public function it_paginates_assets()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
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

    /** @test */
    public function it_queries_blueprint_specific_fields()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
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

    /** @test */
    public function it_sorts_assets()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
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

    /** @test */
    public function it_sorts_assets_descending()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
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

    /** @test */
    public function it_sorts_assets_on_multiple_fields()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
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
