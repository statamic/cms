<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\GlobalFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GraphQL;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class GlobalTest extends TestCase
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
            ->post('/graphql', ['query' => '{globalSet}'])
            ->assertSee('Cannot query field \"globalSet\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_a_global_set_by_handle()
    {
        GlobalFactory::handle('social')->data(['twitter' => '@statamic'])->create();
        GlobalFactory::handle('company')->data(['company_name' => 'Statamic'])->create();
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        $company = Blueprint::makeFromFields(['company_name' => ['type' => 'text']])->setHandle('company')->setNamespace('globals');
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);
        BlueprintRepository::shouldReceive('find')->with('globals.company')->andReturn($company);

        $query = <<<'GQL'
{
    globalSet(handle: "social") {
        handle
        site {
            handle
        }
        ... on GlobalSet_Social {
            twitter
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
            ->assertExactJson(['data' => ['globalSet' => [
                'handle' => 'social',
                'site' => ['handle' => 'en'],
                'twitter' => '@statamic',
            ]]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        $query = <<<'GQL'
{
    globalSet(handle: "social") {
        handle
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'globals')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'globals')->andReturn([])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'handle' => ['Forbidden: social'],
                        ],
                    ],
                ]],
                'data' => [
                    'globalSet' => null,
                ],
            ]);
    }

    #[Test]
    public function it_queries_a_global_set_in_a_specific_site()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
        ]);

        $set = GlobalFactory::handle('social')->sites(['en' => null, 'fr' => null])->data(['twitter' => '@statamic'])->create();
        $variables = $set->makeLocalization('fr')->data(['twitter' => '@statamic_fr'])->save();
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);

        $query = <<<'GQL'
{
    globalSet(handle: "social", site: "fr") {
        handle
        site {
            handle
        }
        ... on GlobalSet_Social {
            twitter
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
            ->assertExactJson(['data' => ['globalSet' => [
                'handle' => 'social',
                'site' => ['handle' => 'fr'],
                'twitter' => '@statamic_fr',
            ]]]);
    }

    #[Test]
    public function it_can_add_custom_fields_to_interface()
    {
        GraphQL::addField('GlobalSetInterface', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('GlobalSetInterface', 'two', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        GlobalFactory::handle('social')->data(['twitter' => '@statamic'])->create();
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);

        $query = <<<'GQL'
{
    globalSet(handle: "social") {
        handle
        one
        two
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'globalSet' => [
                    'handle' => 'social',
                    'one' => 'first',
                    'two' => 'second',
                ],
            ]]);
    }

    #[Test]
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('GlobalSet_Social', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GlobalFactory::handle('social')->data(['twitter' => '@statamic'])->create();
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);

        $query = <<<'GQL'
{
    globalSet(handle: "social") {
        handle
        ... on GlobalSet_Social {
            one
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'globalSet' => [
                    'handle' => 'social',
                    'one' => 'first',
                ],
            ]]);
    }
}
