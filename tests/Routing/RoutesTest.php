<?php

namespace Tests\Routing;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Config;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
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

        $app->booted(function () {
            Route::statamic('/basic-route');

            Route::statamic('/basic-route-with-data', 'test', ['hello' => 'world']);

            Route::statamic('/basic-route-with-view-closure', function () {
                return view('test', ['hello' => 'world']);
            });

            Route::statamic('/basic-route-with-view-closure-and-dependency-injection', function (Request $request, FooClass $foo) {
                return view('test', ['hello' => "view closure dependencies: $request->value $foo->value"]);
            });

            Route::statamic('/basic-route-with-view-closure-and-custom-return', function () {
                return ['message' => 'not a view instance'];
            });

            Route::statamic('/basic-route-with-data-closure', 'test', function () {
                return ['hello' => 'world'];
            });

            Route::statamic('/basic-route-with-data-closure-and-dependency-injection', 'test', function (Request $request, FooClass $foo) {
                return ['hello' => "data closure dependencies: $request->value $foo->value"];
            });

            Route::statamic('/you-cannot-use-data-param-with-view-closure', function () {
                return view('test', ['hello' => 'world']);
            }, 'hello');

            Route::statamic('/basic-route-without-data', 'test');

            Route::statamic('/route/with/placeholders/{foo}/{bar}/{baz}', 'test');

            Route::statamic('/route/with/placeholders/view/closure/{foo}/{bar}/{baz}', function ($foo, $bar, $baz) {
                return view('test', ['hello' => "view closure placeholders: $foo $bar $baz"]);
            });

            Route::statamic('/route/with/placeholders/view/closure-dependency-injection/{baz}/{qux}', function (Request $request, FooClass $foo, BarClass $bar, $baz, $qux) {
                return view('test', ['hello' => "view closure dependencies: $request->value $foo->value $bar->value $baz $qux"]);
            });

            Route::statamic('/route/with/placeholders/view/closure-dependency-order-doesnt-matter/{baz}/{qux}', function (FooClass $foo, $baz, BarClass $bar, Request $request, $qux) {
                return view('test', ['hello' => "view closure dependencies: $request->value $foo->value $bar->value $baz $qux"]);
            });

            Route::statamic('/route/with/placeholders/view/closure-primitive-type-hints/{name}/{age}', function (string $name, int $age) {
                return view('test', ['hello' => "view closure placeholders: $name $age"]);
            });

            Route::statamic('/route/with/placeholders/data/closure/{foo}/{bar}/{baz}', 'test', function ($foo, $bar, $baz) {
                return ['hello' => "data closure placeholders: $foo $bar $baz"];
            });

            Route::statamic('/route/with/placeholders/data/closure-dependency-injection/{baz}/{qux}', 'test', function (Request $request, FooClass $foo, BarClass $bar, $baz, $qux) {
                return ['hello' => "data closure dependencies: $request->value $foo->value $bar->value $baz $qux"];
            });

            Route::statamic('/route/with/placeholders/data/closure-dependency-order-doesnt-matter/{baz}/{qux}', 'test', function (FooClass $foo, $baz, BarClass $bar, Request $request, $qux) {
                return ['hello' => "data closure dependencies: $request->value $foo->value $bar->value $baz $qux"];
            });

            Route::statamic('/route/with/placeholders/data/closure-primitive-type-hints/{name}/{age}', 'test', function (string $name, int $age) {
                return ['hello' => "data closure placeholders: $name $age"];
            });

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

            Route::statamic('/xml', 'feed');

            Route::statamic('/xml-with-custom-type', 'feed', [
                'content_type' => 'json',
            ]);

            Route::middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)->group(function () {

                Route::get('/bindings/entry/{entry}', function ($entry) {
                    return ['title' => $entry->get('title')];
                });

                Route::get('/bindings/entry/slug/{entry:slug}', function ($entry) {
                    return ['title' => $entry->get('title')];
                });

                Route::get('/bindings/term/{term}', function ($term) {
                    return ['title' => $term->get('title')];
                });

                Route::get('/bindings/term/title/{term:slug}', function ($term) {
                    return ['title' => $term->get('title')];
                });

            });
        });
    }

    #[Test]
    public function it_renders_a_view()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data')
            ->assertOk()
            ->assertSee('Hello world');
    }

    #[Test]
    public function it_renders_a_view_implied_from_the_route()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('basic-route', 'Hello world');

        $this->get('/basic-route')
            ->assertOk()
            ->assertSee('Hello world');
    }

    #[Test]
    public function it_renders_a_view_using_a_view_closure()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-view-closure')
            ->assertOk()
            ->assertSee('Hello world');
    }

    #[Test]
    public function it_renders_a_view_using_a_view_closure_with_dependency_injection()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-view-closure-and-dependency-injection?value=request_value')
            ->assertOk()
            ->assertSee('Hello view closure dependencies: request_value foo_class');
    }

    #[Test]
    public function it_renders_a_view_using_a_view_closure_with_dependency_injection_from_container()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        app()->bind(FooClass::class, function () {
            $foo = new FooClass;
            $foo->value = 'foo_modified';

            return $foo;
        });

        $this->get('/basic-route-with-view-closure-and-dependency-injection?value=request_value')
            ->assertOk()
            ->assertSee('Hello view closure dependencies: request_value foo_modified');
    }

    #[Test]
    public function it_renders_a_view_using_a_custom_view_closure_that_does_not_return_a_view_instance()
    {
        $this->get('/basic-route-with-view-closure-and-custom-return')
            ->assertOk()
            ->assertJson([
                'message' => 'not a view instance',
            ]);
    }

    #[Test]
    public function it_renders_a_view_using_a_data_closure()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data-closure')
            ->assertOk()
            ->assertSee('Hello world');
    }

    #[Test]
    public function it_renders_a_view_using_a_data_closure_with_dependency_injection()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data-closure-and-dependency-injection?value=request_value')
            ->assertOk()
            ->assertSee('Hello data closure dependencies: request_value foo_class');
    }

    #[Test]
    public function it_renders_a_view_using_a_data_closure_with_dependency_injection_from_container()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        app()->bind(FooClass::class, function () {
            $foo = new FooClass;
            $foo->value = 'foo_modified';

            return $foo;
        });

        $this->get('/basic-route-with-data-closure-and-dependency-injection?value=request_value')
            ->assertOk()
            ->assertSee('Hello data closure dependencies: request_value foo_modified');
    }

    #[Test]
    public function it_throws_exception_if_you_try_to_pass_data_parameter_when_using_view_closure()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $response = $this
            ->get('/you-cannot-use-data-param-with-view-closure')
            ->assertInternalServerError();

        $this->assertEquals('Parameter [$data] not supported with [$view] closure!', $response->exception->getMessage());
    }

    #[Test]
    public function it_renders_a_view_without_data()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-without-data')
            ->assertOk()
            ->assertSee('Hello ');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ foo }} {{ bar }} {{ baz }}');

        $this->get('/route/with/placeholders/one/two/three')
            ->assertOk()
            ->assertSee('Hello one two three');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_view_closure()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route/with/placeholders/view/closure/one/two/three')
            ->assertOk()
            ->assertSee('Hello view closure placeholders: one two three');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_view_closure_with_dependency_injection()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route/with/placeholders/view/closure-dependency-injection/one/two?value=request_value')
            ->assertOk()
            ->assertSee('Hello view closure dependencies: request_value foo_class bar_class one two');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_view_closure_and_dependency_order_doesnt_matter()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        app()->bind(BarClass::class, function () {
            $foo = new BarClass;
            $foo->value = 'bar_class_modified';

            return $foo;
        });

        $this->get('/route/with/placeholders/view/closure-dependency-order-doesnt-matter/one/two?value=request_value')
            ->assertOk()
            ->assertSee('Hello view closure dependencies: request_value foo_class bar_class_modified one two');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_view_closure_using_primitive_type_hints()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route/with/placeholders/view/closure-primitive-type-hints/darth/42')
            ->assertOk()
            ->assertSee('Hello view closure placeholders: darth 42');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_data_closure()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route/with/placeholders/data/closure/one/two/three')
            ->assertOk()
            ->assertSee('Hello data closure placeholders: one two three');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_data_closure_with_dependency_injection()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route/with/placeholders/data/closure-dependency-injection/one/two?value=request_value')
            ->assertOk()
            ->assertSee('Hello data closure dependencies: request_value foo_class bar_class one two');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_data_closure_and_dependency_order_doesnt_matter()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        app()->bind(BarClass::class, function () {
            $foo = new BarClass;
            $foo->value = 'bar_class_modified';

            return $foo;
        });

        $this->get('/route/with/placeholders/data/closure-dependency-order-doesnt-matter/one/two?value=request_value')
            ->assertOk()
            ->assertSee('Hello data closure dependencies: request_value foo_class bar_class_modified one two');
    }

    #[Test]
    public function it_renders_a_view_with_placeholders_using_a_data_closure_using_primitive_type_hints()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route/with/placeholders/data/closure-primitive-type-hints/darth/42')
            ->assertOk()
            ->assertSee('Hello data closure placeholders: darth 42');
    }

    #[Test]
    public function it_renders_a_view_with_custom_layout()
    {
        $this->viewShouldReturnRaw('custom-layout', 'Custom layout {{ template_content }}');
        $this->viewShouldReturnRaw('layout', 'Default layout');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route-with-custom-layout')
            ->assertOk()
            ->assertSee('Custom layout Hello world');
    }

    #[Test]
    #[DataProvider('undefinedLayoutRouteProvider')]
    public function it_renders_a_view_without_a_layout($route)
    {
        $this->viewShouldReturnRaw('layout', 'The layout {{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get($route)
            ->assertOk()
            ->assertSee('Hello world')
            ->assertDontSee('The layout');
    }

    public static function undefinedLayoutRouteProvider()
    {
        return [
            'null' => ['route-with-null-layout'],
            'false' => ['route-with-false-layout'],
        ];
    }

    #[Test]
    public function it_loads_content()
    {
        EntryFactory::id('pages-blog')->collection('pages')->data(['title' => 'Blog'])->create();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }} {{ title }} {{ id }}');

        $this->get('/route-with-loaded-entry')
            ->assertOk()
            ->assertSee('Hello world Blog pages-blog');
    }

    #[Test]
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

    #[Test]
    public function it_renders_a_view_with_custom_content_type()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', '{"hello":"{{ hello }}"}');

        $this->get('/route-with-custom-content-type')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['hello' => 'world']);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function xml_template_with_custom_content_type_does_not_change_to_xml()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');

        $this
            ->get('/xml-with-custom-type')
            ->assertHeader('Content-Type', 'application/json');
    }

    #[Test]
    public function it_loads_entry_by_binding()
    {
        Config::set('statamic.routes.bindings', true);

        $collection = Collection::make('pages')->save();
        EntryFactory::id('pages-blog')->collection($collection)->slug('blog')->data(['title' => 'Blog'])->create();

        $this->get('/bindings/entry/pages-blog')
            ->assertOk()
            ->assertJson(['title' => 'Blog']);

        $this->get('/bindings/entry/slug/blog')
            ->assertOk()
            ->assertJson(['title' => 'Blog']);

        $this->get('/bindings/entry/slug/blog2')
            ->assertNotFound();
    }

    #[Test]
    public function it_loads_term_by_binding()
    {
        Config::set('statamic.routes.bindings', true);

        $taxonomy = Taxonomy::make('pages')->save();
        Term::make()->taxonomy('pages')->slug('blog')->data(['title' => 'Blog'])->save();

        $this->get('/bindings/term/pages::blog')
            ->assertOk()
            ->assertJson(['title' => 'Blog']);

        $this->get('/bindings/term/title/Blog')
            ->assertOk()
            ->assertJson(['title' => 'Blog']);

        $this->get('/bindings/term/title/Blog2')
            ->assertNotFound();
    }

    #[Test]
    public function it_uses_a_non_default_layout()
    {
        config()->set('statamic.system.layout', 'custom-layout');
        $this->viewShouldReturnRaw('custom-layout', 'Custom layout {{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data')
            ->assertOk()
            ->assertSee('Custom layout');
    }
}

class FooClass
{
    public $value = 'foo_class';
}

class BarClass
{
    public $value = 'bar_class';
}
