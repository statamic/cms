<?php

namespace Tests\Feature\GraphQL;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use Tests\TestCase;

/** @group graphql */
class CustomQueryTest extends TestCase
{
    /** @test **/
    public function custom_query_does_not_yet_exist()
    {
        $this
            ->post('/graphql', ['query' => '{foo}'])
            ->assertJson(['errors' => [[
                'message' => 'Cannot query field "foo" on type "Query".',
            ]]]);
    }

    /**
     * @test
     *
     * @environment-setup addCustomQueryWithMethod
     **/
    public function a_custom_query_can_be_added_to_the_default_schema()
    {
        $this
            ->post('/graphql', ['query' => '{foo}'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['foo' => 'bar']]);
    }

    protected function addCustomQueryWithMethod($app)
    {
        GraphQL::addQuery(FooQuery::class);
    }

    /**
     * @test
     *
     * @environment-setup addCustomQueryThroughConfig
     **/
    public function a_custom_query_can_be_added_to_the_default_schema_through_config()
    {
        $this
            ->post('/graphql', ['query' => '{foo}'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['foo' => 'bar']]);
    }

    protected function addCustomQueryThroughConfig($app)
    {
        $app['config']->set('statamic.graphql.queries', [FooQuery::class]);
    }
}

class FooQuery extends Query
{
    protected $attributes = [
        'name' => 'foo',
    ];

    public function type(): Type
    {
        return GraphQL::string();
    }

    public function resolve()
    {
        return 'bar';
    }
}
