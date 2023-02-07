<?php

namespace Tests\Routing;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;
use Mockery as m;
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

    /** @test */
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

    /** @test */
    public function amp_routes_are_registered()
    {
        config(['statamic.amp.enabled' => true]);

        $this->assertCount(0, $this->router->getRoutes()->get());

        $this->router->amp(function () {
            $this->router->statamic('named-uri', 'view-name', ['foo' => 'bar'])->name('test');
            $this->router->statamic('unnamed-uri', 'view-name', ['foo' => 'bar']);
        });

        $routes = $this->router->getRoutes()->get();
        $this->assertCount(4, $routes);

        tap($routes[0], function ($route) {
            $this->commonRouteAssertions($route);
            $this->assertEquals('named-uri', $route->uri());
            $this->assertEquals('test', $route->getName());
        });

        tap($routes[1], function ($route) {
            $this->commonRouteAssertions($route);
            $this->assertEquals('unnamed-uri', $route->uri());
            $this->assertNull($route->getName());
        });

        tap($routes[2], function ($route) {
            $this->commonRouteAssertions($route);
            $this->assertEquals('amp/named-uri', $route->uri());
            $this->assertEquals('test.amp', $route->getName());
        });

        tap($routes[3], function ($route) {
            $this->commonRouteAssertions($route);
            $this->assertEquals('amp/unnamed-uri', $route->uri());
            $this->assertNull($route->getName());
        });
    }

    /** @test */
    public function amp_routes_do_not_get_registered_if_amp_is_disabled()
    {
        config(['statamic.amp.enabled' => false]);

        $this->assertCount(0, $this->router->getRoutes()->get());

        $this->router->amp(function () {
            $this->router->statamic('the-uri', 'view-name', ['foo' => 'bar']);
        });

        $routes = $this->router->getRoutes()->get();
        $this->assertCount(1, $routes);

        tap($routes[0], function ($route) {
            $this->commonRouteAssertions($route);
            $this->assertEquals('the-uri', $route->uri());
        });
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
