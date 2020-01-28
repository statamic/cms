<?php

namespace Tests\Routing;

use Illuminate\Support\Facades\Route;
use Tests\FakesViews;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use FakesViews;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::statamic('/basic-route-with-data', 'test', ['hello' => 'world']);

            Route::statamic('/basic-route-without-data', 'test');

            Route::statamic('/route-with-custom-layout', 'test', [
                'layout' => 'custom-layout',
                'hello' => 'world'
            ]);
        });
    }

    /** @test */
    function it_renders_a_view()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data')
            ->assertOk()
            ->assertSee('Hello world');
    }

    /** @test */
    function it_renders_a_view_without_data()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-without-data')
            ->assertOk()
            ->assertSee('Hello ');
    }

    /** @test */
    function it_renders_a_view_with_custom_layout()
    {
        $this->viewShouldReturnRaw('custom-layout', 'Custom layout {{ template_content }}');
        $this->viewShouldReturnRaw('layout', 'Default layout');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route-with-custom-layout')
            ->assertOk()
            ->assertSee('Custom layout Hello world');
    }
}
