<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\GlobalFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class GlobalsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['globals'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    /**
     * @test
     *
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{globalSets}'])
            ->assertSee('Cannot query field \"globalSets\" on type \"Query\"', false);
    }

    /** @test */
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

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['globalSets' => [
                ['handle' => 'social', 'twitter' => '@statamic'],
                ['handle' => 'company', 'company_name' => 'Statamic'],
            ]]]);
    }
}
