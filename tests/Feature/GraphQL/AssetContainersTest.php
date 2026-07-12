<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class AssetContainersTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['assets'];

    public function setUp(): void
    {
        parent::setUp();

        AssetContainer::make('public')->title('Public')->save();
        AssetContainer::make('private')->title('Private')->save();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{assetContainers}'])
            ->assertSee('Cannot query field \"assetContainers\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_asset_containers()
    {
        $query = <<<'GQL'
{
    assetContainers {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(AssetContainer::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assetContainers' => [
                ['handle' => 'public', 'title' => 'Public'],
                ['handle' => 'private', 'title' => 'Private'],
            ]]]);
    }

    #[Test]
    public function it_queries_only_allowed_sub_resources()
    {
        $query = <<<'GQL'
{
    assetContainers {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'assets')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'assets')->andReturn(['public'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['assetContainers' => [
                ['handle' => 'public', 'title' => 'Public'],
            ]]]);
    }
}
