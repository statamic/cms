<?php

namespace Tests\Feature\GraphQL;

use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\GraphQL;
use Tests\TestCase;

#[Group('graphql')]
class CustomMiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app()->instance('request-count', 0);
    }

    #[Test]
    public function custom_middleware_does_not_yet_exist()
    {
        $this->post('/graphql', ['query' => '{ping}']);

        $this->assertEquals(0, app('request-count'));
    }

    #[Test]
    #[DefineEnvironment('addCustomMiddlewareWithMethod')]
    public function a_custom_middleware_can_be_added_to_the_default_schema()
    {
        $this->post('/graphql', ['query' => '{ping}']);

        $this->assertEquals(1, app('request-count'));
    }

    protected function addCustomMiddlewareWithMethod($app)
    {
        GraphQL::addMiddleware(CountRequests::class);
    }

    #[Test]
    #[DefineEnvironment('addCustomMiddlewareWithMethod')]
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
