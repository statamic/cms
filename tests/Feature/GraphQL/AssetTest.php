<?php

namespace Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
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
}
