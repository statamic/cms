<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class AssetContainersTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_asset_containers()
    {
        AssetContainer::make('public')->title('Public')->save();
        AssetContainer::make('private')->title('Private')->save();

        $query = <<<'GQL'
{
    assetContainers {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assetContainers' => [
                ['handle' => 'public', 'title' => 'Public'],
                ['handle' => 'private', 'title' => 'Private'],
            ]]]);
    }
}
