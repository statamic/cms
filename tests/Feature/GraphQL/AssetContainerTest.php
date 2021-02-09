<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class AssetContainerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_an_asset_container_by_handle()
    {
        AssetContainer::make('public')->title('Public')->save();
        AssetContainer::make('private')->title('Private')->save();

        $query = <<<'GQL'
{
    assetContainer(handle: "private") {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'assetContainer' => [
                    'handle' => 'private',
                    'title' => 'Private',
                ],
            ]]);
    }
}
