<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\GlobalFactory;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GraphQL;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class GlobalTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
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
        ... on GlobalSet_Social {
            twitter
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['globalSet' => [
                'handle' => 'social',
                'twitter' => '@statamic',
            ]]]);
    }

    /** @test */
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

    /** @test */
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
