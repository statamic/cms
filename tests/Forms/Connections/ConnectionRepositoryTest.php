<?php

namespace Tests\Forms\Connections;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\FormConnection;
use Statamic\Forms\Connections\Connection;
use Statamic\Support\VueComponent;
use Tests\TestCase;

class ConnectionRepositoryTest extends TestCase
{
    #[Test]
    public function it_gets_a_connection()
    {
        RoutedConnection::register();

        $this->assertInstanceOf(RoutedConnection::class, FormConnection::find('routed'));
        $this->assertTrue(FormConnection::all()->contains(fn ($connection) => $connection instanceof RoutedConnection));
        $this->assertNull(FormConnection::find('unknown'));
    }

    #[Test]
    public function it_registers_routes_with_authorization()
    {
        RoutedConnection::register();

        FormConnection::routes();

        $route = collect(Route::getRoutes())->first(fn ($route) => $route->getName() === 'forms.connect.routed.process');

        $this->assertNotNull($route);
        $this->assertEquals('forms/{form}/connect/routed', $route->uri());
        $this->assertContains('can:edit,form', $route->middleware());
    }
}

class RoutedConnection extends Connection
{
    public function render($form): VueComponent
    {
        return VueComponent::render('routed-connection');
    }

    public function routes($router): void
    {
        $router->post('/', fn () => 'processed')->name('process');
    }
}
