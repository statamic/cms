<?php

namespace Tests\Routing;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;
use Mockery as m;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Mixins\Router as RouterMixin;
use Tests\TestCase;

class RouterMixinTest extends TestCase
{
    private $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->router = new Router(m::mock(Dispatcher::class), Container::getInstance());
        $this->router->mixin(new RouterMixin);
    }

    #[Test]
    public function statamic_routes_are_registered()
    {
        $this->assertCount(0, $this->router->getRoutes()->get());

        $this->router->statamic('the-uri', 'view-name', ['foo' => 'bar']);

        $routes = $this->router->getRoutes()->get();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->commonRouteAssertions($route);
        $this->assertEquals('the-uri', $route->uri());
    }

    public function commonRouteAssertions($route)
    {
        $this->assertEquals(['GET', 'HEAD'], $route->methods());

        $this->assertEquals([
            'view' => 'view-name',
            'data' => ['foo' => 'bar'],
        ], $route->defaults);

        $this->assertEquals(FrontendController::class.'@route', $route->getActionName());
    }
}
