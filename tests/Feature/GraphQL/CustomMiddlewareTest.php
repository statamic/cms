<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\GraphQL;
use Tests\TestCase;

/** @group graphql */
class CustomMiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app()->instance('request-count', 0);
    }

    /** @test **/
    public function custom_middleware_does_not_yet_exist()
    {
        $this->post('/graphql', ['query' => '{ping}']);

        $this->assertEquals(0, app('request-count'));
    }

    /**
     * @test
     *
     * @environment-setup addCustomMiddlewareWithMethod
     **/
    public function a_custom_middleware_can_be_added_to_the_default_schema()
    {
        $this->post('/graphql', ['query' => '{ping}']);

        $this->assertEquals(1, app('request-count'));
    }

    protected function addCustomMiddlewareWithMethod($app)
    {
        GraphQL::addMiddleware(CountRequests::class);
    }

    /**
     * @test
     *
     * @environment-setup addCustomMiddlewareThroughConfig
     **/
    public function a_custom_middleware_can_be_added_to_the_default_schema_through_config()
    {
        $this->post('/graphql', ['query' => '{ping}']);

        $this->assertEquals(1, app('request-count'));
    }

    protected function addCustomMiddlewareThroughConfig($app)
    {
        $app['config']->set('statamic.graphql.middleware', [CountRequests::class]);
    }
}

class CountRequests
{
    public function handle($request, $next)
    {
        $count = app('request-count');

        $count++;

        app()->instance('request-count', $count);

        return $next($request);
    }
}
