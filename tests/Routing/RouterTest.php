<?php namespace Tests\Routing;

use Tests\TestCase;
use Statamic\Routing\Route;
use Statamic\Routing\Router;

class RouterTest extends TestCase
{
    /** @test */
    public function can_detect_unnamed_wildcards()
    {
        $router = new Router;

        $this->assertTrue($router->hasUnnamedWildcard('/foo/*'));
        $this->assertFalse($router->hasUnnamedWildcard('/foo/{bar}'));
        $this->assertFalse($router->hasUnnamedWildcard('/foo/bar'));
    }

    /** @test */
    public function unnamed_wildcards_get_index_based_names_added()
    {
        $router = new Router;

        $this->assertEquals(
            '/foo/{wildcard_1}/{wildcard_2}',
            $router->nameUnnamedWildcards('/foo/*/*')
        );
    }

    /** @test */
    public function can_detect_named_wildcards()
    {
        $router = new Router;

        $this->assertFalse($router->hasNamedWildcard('/foo/*'));
        $this->assertTrue($router->hasNamedWildcard('/foo/{bar}'));
        $this->assertFalse($router->hasNamedWildcard('/foo/bar'));
    }

    /** @test */
    public function can_detect_wildcards()
    {
        $router = new Router;

        $this->assertTrue($router->hasWildcard('/foo/*'));
        $this->assertTrue($router->hasWildcard('/foo/{bar}'));
        $this->assertFalse($router->hasWildcard('/foo/bar'));
    }

    /** @test */
    public function extracts_wildcard_names()
    {
        $router = new Router;

        $this->assertEquals(
            ['foo', 'bar'],
            $router->extractWildcardNames('/foo/{foo}/{bar}/baz')
        );
    }

    /** @test */
    public function standardizes_routes()
    {
        $router = new Router;

        $routes = [
            '/foo' => 'foo',
            '/bar' => ['template' => 'bar', 'title' => 'Bar page'],
        ];

        $expected = [
            '/foo' => ['template' => 'foo'],
            '/bar' => ['template' => 'bar', 'title' => 'Bar page'],
        ];

        $this->assertEquals($expected, $router->standardize($routes));
    }

    /** @test */
    public function gets_route_matches()
    {
        $router = new Router;

        $this->assertFalse(
            $router->getRouteMatches('/test/{foo}', '/not-a-matching-url')
        );

        $this->assertEquals(
            ['one', 'two'],
            $router->getRouteMatches('/test/{foo}/{bar}', '/test/one/two')
        );
    }

    /** @test */
    public function gets_an_exact_route()
    {
        $router = new Router;

        $matchingRoute = $router->getExactRoute('/test', '/test', [
            'foo' => 'bar'
        ]);

        $unmatchedRoute = $router->getExactRoute('/test', '/something-else', []);

        $this->assertInstanceOf(Route::class, $matchingRoute);
        $this->assertEquals('bar', $matchingRoute->get('foo'));
        $this->assertNull($unmatchedRoute);
    }

    /** @test */
    public function gets_a_wildcard_route()
    {
        $router = new Router;

        $named = $router->getWildcardRoute('/test/{one}/{two}', '/test/foo/bar', [
            'baz' => 'qux'
        ]);

        $unnamed = $router->getWildcardRoute('/test/*/*', '/test/foo/bar', [
            'baz' => 'qux'
        ]);

        $unmatchedRoute = $router->getWildcardRoute('/test/{one}/{two}', '/something-else', []);

        $this->assertInstanceOf(Route::class, $named);
        $this->assertEquals('foo', $named->get('one'));
        $this->assertEquals('bar', $named->get('two'));
        $this->assertEquals('qux', $named->get('baz'));

        $this->assertInstanceOf(Route::class, $unnamed);
        $this->assertEquals('foo', $unnamed->get('wildcard_1'));
        $this->assertEquals('bar', $unnamed->get('wildcard_2'));
        $this->assertEquals('qux', $unnamed->get('baz'));

        $this->assertNull($unmatchedRoute);
    }

    /** @test */
    public function gets_a_route()
    {
        $router = new Router([
            '/foo' => 'foo',
            '/bar' => ['template' => 'bar', 'title' => 'Bar page'],
            '/test/{foo}/{bar}' => 'foo'
        ]);

        $this->assertInstanceOf(Route::class, $router->getRoute('/test/one/two'));
        $this->assertInstanceOf(Route::class, $router->getRoute('/foo'));
        $this->assertNull($router->getRoute('/non-existent-route'));
    }
}
