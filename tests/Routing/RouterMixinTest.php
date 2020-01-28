<?php

namespace Tests\Routing;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Mockery as m;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Mixins\Router as RouterMixin;
use Tests\TestCase;

class RouterMixinTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->router = new Router(m::mock(Dispatcher::class), Container::getInstance());
        $this->router->mixin(new RouterMixin);
    }

    /** @test */
    function statamic_routes_are_registered()
    {
        $this->assertCount(0, $this->router->getRoutes()->get());

        $this->router->statamic('the-uri', 'view-name', ['foo' => 'bar']);

        $routes = $this->router->getRoutes()->get();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->commonRouteAssertions($route);
        $this->assertEquals('the-uri', $route->uri());
    }

    function commonRouteAssertions($route)
    {
        $this->assertEquals(['GET', 'HEAD'], $route->methods());

        $this->assertEquals([
            'view' => 'view-name',
            'data' => ['foo' => 'bar'],
        ], $route->defaults);

        $this->assertEquals([
            'uses' => FrontendController::class.'@route',
            'controller' => FrontendController::class.'@route',
        ], $route->getAction());
    }
}
