<?php

namespace Tests\Forms\Connectors;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Facades\FormConnector;
use Statamic\Fields\Blueprint;
use Statamic\Forms\Connectors\Connector;
use Statamic\Support\VueComponent;
use Tests\TestCase;

class ConnectorRepositoryTest extends TestCase
{
    #[Test]
    public function it_gets_a_connector()
    {
        RoutedConnector::register();

        $this->assertInstanceOf(RoutedConnector::class, FormConnector::find('routed'));
        $this->assertTrue(FormConnector::all()->contains(fn ($connector) => $connector instanceof RoutedConnector));
        $this->assertNull(FormConnector::find('unknown'));
    }

    #[Test]
    public function it_registers_routes_with_authorization()
    {
        RoutedConnector::register();

        FormConnector::routes();

        $route = collect(Route::getRoutes())->first(fn ($route) => $route->getName() === 'forms.connect.routed.process');

        $this->assertNotNull($route);
        $this->assertEquals('forms/{form}/connect/routed', $route->uri());
        $this->assertContains('can:edit,form', $route->middleware());
    }
}

class RoutedConnector extends Connector
{
    public function render(): VueComponent
    {
        return VueComponent::render('routed-connector');
    }

    public function blueprint(): Blueprint
    {
        return BlueprintFacade::make();
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        return null;
    }

    public function routes($router): void
    {
        $router->post('/', fn () => 'processed')->name('process');
    }
}
