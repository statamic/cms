<?php

namespace Tests\Routing;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Collection;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.amp.enabled', true);

        $app->booted(function () {
            Route::statamic('/basic-route-with-data', 'test', ['hello' => 'world']);

            Route::statamic('/basic-route-without-data', 'test');

            Route::statamic('/route/with/placeholders/{foo}/{bar}/{baz}', 'test');

            Route::statamic('/route-with-custom-layout', 'test', [
                'layout' => 'custom-layout',
                'hello' => 'world',
            ]);

            Route::statamic('/route-with-loaded-entry', 'test', [
                'hello' => 'world',
                'load' => 'pages-blog',
            ]);

            Route::statamic('/route-with-loaded-entry-by-uri', 'test', [
                'hello' => 'world',
                'load' => '/blog',
            ]);

            Route::statamic('/route-with-custom-content-type', 'test', [
                'hello' => 'world',
                'content_type' => 'json',
            ]);

            Route::amp(function () {
                Route::statamic('/route-with-amp', 'test');
            });
        });
    }

    /** @test */
    public function it_renders_a_view()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data')
            ->assertOk()
            ->assertSee('Hello world');
    }

    /** @test */
    public function it_renders_a_view_without_data()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-without-data')
            ->assertOk()
            ->assertSee('Hello ');
    }

    /** @test */
    public function it_renders_a_view_with_placeholders()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ foo }} {{ bar }} {{ baz }}');

        $this->get('/route/with/placeholders/one/two/three')
            ->assertOk()
            ->assertSee('Hello one two three');
    }

    /** @test */
    public function it_renders_a_view_with_custom_layout()
    {
        $this->viewShouldReturnRaw('custom-layout', 'Custom layout {{ template_content }}');
        $this->viewShouldReturnRaw('layout', 'Default layout');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route-with-custom-layout')
            ->assertOk()
            ->assertSee('Custom layout Hello world');
    }

    /** @test */
    public function it_loads_content()
    {
        EntryFactory::id('pages-blog')->collection('pages')->data(['title' => 'Blog'])->create();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }} {{ title }} {{ id }}');

        $this->get('/route-with-loaded-entry')
            ->assertOk()
            ->assertSee('Hello world Blog pages-blog');
    }

    /** @test */
    public function it_loads_content_by_uri()
    {
        $collection = Collection::make('pages')->routes('/{slug}')->save();
        EntryFactory::id('pages-blog')->collection($collection)->slug('blog')->data(['title' => 'Blog'])->create();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }} {{ title }} {{ id }}');

        $this->get('/route-with-loaded-entry-by-uri')
            ->assertOk()
            ->assertSee('Hello world Blog pages-blog');
    }

    /** @test */
    public function it_loads_amp_route()
    {
        $this->viewShouldReturnRaw('layout', '');
        $this->viewShouldReturnRaw('test', '');

        $this->get('/route-with-amp')->assertOk();
        $this->get('/amp/route-with-amp')->assertOk();
        $this->get('/amp/basic-route-with-data')->assertNotFound();
    }

    /** @test */
    public function it_renders_a_view_with_custom_content_type()
    {
        $this->withoutExceptionHandling();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', '{"hello":"{{ hello }}"}');

        $this->get('/route-with-custom-content-type')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['hello' => 'world']);
    }
}
