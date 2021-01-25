<?php

namespace Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class AssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_an_asset_by_id()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::b.txt") {
        id
        path
        extension
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'id' => 'test::b.txt',
                    'path' => 'b.txt',
                    'extension' => 'txt',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_asset_by_container_and_path()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(container: "test", path: "b.txt") {
        path
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'path' => 'b.txt',
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_interface()
    {
        GraphQL::addField('AssetInterface', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('AssetInterface', 'two', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        GraphQL::addField('AssetInterface', 'extension', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden extension';
                },
            ];
        });

        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
        Storage::disk('test')->put('a.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::a.txt") {
        path
        one
        two
        extension
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'path' => 'a.txt',
                    'one' => 'first',
                    'two' => 'second',
                    'extension' => 'the overridden extension',
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('Asset_Test', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('Asset_Test', 'extension', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden extension';
                },
            ];
        });

        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
        Storage::disk('test')->put('a.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::a.txt") {
        path
        ... on Asset_Test {
            one
            extension
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'path' => 'a.txt',
                    'one' => 'first',
                    'extension' => 'the overridden extension',
                ],
            ]]);
    }

    /** @test */
    public function adding_custom_field_to_an_implementation_does_not_add_it_to_the_interface()
    {
        GraphQL::addField('Asset_Test', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
        Storage::disk('test')->put('a.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::a.txt") {
        path
        one
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson(['errors' => [[
                'message' => 'Cannot query field "one" on type "AssetInterface". Did you mean to use an inline fragment on "Asset_Test"?',
            ]]]);
    }
}
