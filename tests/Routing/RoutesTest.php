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

            Route::statamic('/route-with-null-layout', 'test', [
                'layout' => null,
                'hello' => 'world',
            ]);

            Route::statamic('/route-with-false-layout', 'test', [
                'layout' => false,
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

            Route::statamic('/xml', 'feed');

            Route::statamic('/xml-with-custom-type', 'feed', [
                'content_type' => 'json',
            ]);
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

    /**
     * @test
     *
     * @dataProvider undefinedLayoutRouteProvider
     **/
    public function it_renders_a_view_without_a_layout($route)
    {
        $this->withoutExceptionHandling();
        $this->viewShouldReturnRaw('layout', 'The layout {{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get($route)
            ->assertOk()
            ->assertSee('Hello world')
            ->assertDontSee('The layout');
    }

    public function undefinedLayoutRouteProvider()
    {
        return [
            'null' => ['route-with-null-layout'],
            'false' => ['route-with-false-layout'],
        ];
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

    /** @test */
    public function xml_antlers_template_with_xml_layout_will_use_both_and_change_the_content_type()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');

        $response = $this
            ->get('/xml')
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $this->assertEquals('<?xml ?><foo></foo>', $response->getContent());
    }

    /** @test */
    public function xml_antlers_template_with_non_xml_layout_will_change_content_type_but_avoid_using_the_layout()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html>{{ template_content }}</html>', 'antlers.html');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');

        $response = $this
            ->get('/xml')
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $this->assertEquals('<foo></foo>', $response->getContent());
    }

    /** @test */
    public function xml_antlers_layout_will_change_the_content_type()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.html');

        $response = $this
            ->get('/xml')
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $this->assertEquals('<?xml ?><foo></foo>', $response->getContent());
    }

    /** @test */
    public function xml_blade_template_will_not_change_content_type()
    {
        // Blade doesnt support xml files, but even if it did,
        // we only want it to happen when using Antlers.

        $this->withFakeViews();
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'blade.xml');

        $response = $this
            ->get('/xml')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $this->assertEquals('<foo></foo>', $response->getContent());
    }

    /** @test */
    public function xml_template_with_custom_content_type_does_not_change_to_xml()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');

        $this
            ->get('/xml-with-custom-type')
            ->assertHeader('Content-Type', 'application/json');
    }
}
