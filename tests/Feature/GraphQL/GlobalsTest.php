<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\GlobalFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class GlobalsTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['globals'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'globals')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'globals')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{globalSets}'])
            ->assertSee('Cannot query field \"globalSets\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_global_sets()
    {
        GlobalFactory::handle('social')->data(['twitter' => '@statamic'])->create();
        GlobalFactory::handle('company')->data(['company_name' => 'Statamic'])->create();
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        $company = Blueprint::makeFromFields(['company_name' => ['type' => 'text']])->setHandle('company')->setNamespace('globals');
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);
        BlueprintRepository::shouldReceive('find')->with('globals.company')->andReturn($company);

        $query = <<<'GQL'
{
    globalSets {
        handle
        ... on GlobalSet_Social {
            twitter
        }
        ... on GlobalSet_Company {
            company_name
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'globals')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'globals')->andReturn(GlobalSet::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['globalSets' => [
                ['handle' => 'social', 'twitter' => '@statamic'],
                ['handle' => 'company', 'company_name' => 'Statamic'],
            ]]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        GlobalFactory::handle('social')->data(['twitter' => '@statamic'])->create();
        GlobalFactory::handle('company')->data(['company_name' => 'Statamic'])->create();
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        $company = Blueprint::makeFromFields(['company_name' => ['type' => 'text']])->setHandle('company')->setNamespace('globals');
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);
        BlueprintRepository::shouldReceive('find')->with('globals.company')->andReturn($company);

        $query = <<<'GQL'
{
    globalSets {
        handle
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'globals')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'globals')->andReturn(['social'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['globalSets' => [
                ['handle' => 'social'],
            ]]]);
    }
}
