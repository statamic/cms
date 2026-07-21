<?php

namespace Tests\Forms\Connections;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\FormConnection;
use Statamic\Forms\Connections\Connection;
use Statamic\Forms\Connections\ConnectionRepository;
use Tests\TestCase;

class ConnectionRepositoryTest extends TestCase
{
    #[Test]
    public function it_registers_a_connection()
    {
        $connections = new ConnectionRepository;
        $this->assertInstanceOf(Collection::class, $connections->all());
        $this->assertCount(0, $connections->all());

        $connection = $connections->make('one');
        $this->assertCount(0, $connections->all());

        $connections->register($connection);
        $this->assertEquals(['one' => $connection], $connections->all()->all());
        $this->assertEquals($connection, $connections->find('one'));
    }

    #[Test]
    public function it_registers_a_connection_via_a_string()
    {
        $connections = new ConnectionRepository;

        $connection = $connections->register('one');

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('one', $connection->handle());
        $this->assertCount(1, $connections->all());
        $this->assertEquals(['one' => $connection], $connections->all()->all());
    }

    #[Test]
    public function it_registers_a_connection_through_the_facade()
    {
        $connection = FormConnection::register('zapier')->title('Zapier');

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals($connection, FormConnection::find('zapier'));
    }

    #[Test]
    public function it_defers_registration_until_boot_using_extend_method()
    {
        $connections = new ConnectionRepository;
        $callbackRan = false;

        $connections->extend(function ($arg) use ($connections, &$callbackRan) {
            $this->assertEquals($connections, $arg);
            $callbackRan = true;
        });

        $this->assertFalse($callbackRan);

        $connections->boot();

        $this->assertTrue($callbackRan);
    }

    #[Test]
    public function it_registers_core_connections_on_boot()
    {
        FormConnection::boot();

        $email = FormConnection::find('email');
        $webhook = FormConnection::find('webhook');

        $this->assertEquals('Email', $email->title());
        $this->assertEquals('email-connection', $email->component());
        $this->assertEquals('Webhook', $webhook->title());
        $this->assertEquals('webhook-connection', $webhook->component());
    }

    #[Test]
    public function it_registers_routes_wrapped_in_form_edit_authorization()
    {
        FormConnection::extend(function ($connections) {
            $connections->register('zapier')->routes(function ($router) {
                $router->post('/', fn () => 'processed')->name('process');
            });
        });

        FormConnection::routes();

        $route = collect(Route::getRoutes())->first(fn ($route) => $route->getName() === 'forms.connect.zapier.process');

        $this->assertNotNull($route);
        $this->assertEquals('forms/{form}/connect/zapier', $route->uri());
        $this->assertContains('can:edit,form', $route->middleware());
    }

    #[Test]
    public function connections_without_route_closures_get_no_routes()
    {
        FormConnection::extend(function ($connections) {
            $connections->register('zapier');
        });

        FormConnection::routes();

        $routes = collect(Route::getRoutes())->filter(
            fn ($route) => str_starts_with($route->getName() ?? '', 'forms.connect.zapier.')
        );

        $this->assertCount(0, $routes);
    }

    #[Test]
    public function booting_more_than_once_just_updates_the_connections()
    {
        $connections = new ConnectionRepository;

        $connections->extend(function ($connections) {
            $connections->register('test')
                ->title(__('and')); // using a translation that will likely never change.
        });

        $connections->boot();

        $this->assertEquals(['and'], $connections->all()->map->title()->values()->all());

        app()->setLocale('fr');

        $connections->boot();

        $this->assertEquals(['et'], $connections->all()->map->title()->values()->all());
    }
}
