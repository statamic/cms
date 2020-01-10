<?php

namespace Tests;

use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Statamic\Events\ResponseCreated;
use Illuminate\Support\Facades\Event;
use Facades\Tests\Factories\EntryFactory;

class FrontendTest extends TestCase
{
    use FakesRoles;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Blueprint::shouldReceive('find')->with('empty')->andReturn(new \Statamic\Fields\Blueprint);
        $this->addToAssertionCount(-1);

        $this->withStandardFakeViews();
    }

    /** @test */
    function vanity_routes_get_redirected()
    {
        config(['statamic.routes.vanity' => ['/foo' => '/foobar']]);

        $this->get('/foo')->assertStatus(302)->assertRedirect('/foobar');
    }

    /** @test */
    function permanent_redirects_get_redirected()
    {
        config(['statamic.routes.redirect' => ['/foo' => '/foobar']]);

        $this->get('/foo')->assertStatus(301)->assertRedirect('/foobar');
    }

    /** @test */
    function page_is_displayed()
    {
        $this->withoutExceptionHandling();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> <p>{{ content }}</p>');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'template' => 'some_template',
            ]
        ]);

        $response = $this->get('/about')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
    }

    /** @test */
    function page_is_displayed_with_query_string()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> <p>{{ content }}</p>');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'template' => 'some_template',
            ]
        ]);

        $response = $this->get('/about?some=querystring')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
    }

    /** @test */
    function drafts_are_not_visible()
    {
        $this->withStandardFakeErrorViews();
        $this->createPage('about')->published(false)->save();

        $this->get('/about')->assertStatus(404);
    }

    /** @test */
    function drafts_are_visible_if_using_live_preview()
    {
        $this->setTestRoles(['draft_viewer' => ['view drafts on frontend']]);
        $user = User::make()->assignRole('draft_viewer');

        $this->createPage('about')->published(false)->set('content', 'Testing 123')->save();

        $response = $this
            ->actingAs($user)
            ->get('/about', ['X-Statamic-Live-Preview' => true])
            ->assertStatus(200)
            ->assertHeader('X-Statamic-Draft', true);

        $this->assertEquals('Testing 123', $response->content());
    }

    /** @test */
    function drafts_dont_get_statically_cached()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function future_private_entries_are_not_viewable()
    {
        Carbon::setTestNow(Carbon::parse('2019-01-01'));
        $this->withStandardFakeErrorViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRendered('default', 'The template contents');

        $collection = tap($this->makeCollection()->dated(true))->save();
        tap($this->makePage('about')->date('2019-01-02'))->save();

        $this
            ->get('/about')
            ->assertStatus(200)
            ->assertSee('The template contents');

        tap($collection->futureDateBehavior('private'))->save();

        $this
            ->get('/about')
            ->assertStatus(404);
    }

    /** @test */
    function past_private_entries_are_not_viewable()
    {
        Carbon::setTestNow(Carbon::parse('2019-01-01'));
        $this->withStandardFakeErrorViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRendered('default', 'The template contents');

        $collection = tap($this->makeCollection()->dated(true))->save();
        tap($this->makePage('about')->date('2018-01-01'))->save();

        $this
            ->get('/about')
            ->assertStatus(200)
            ->assertSee('The template contents');

        tap($collection->pastDateBehavior('private'))->save();

        $this
            ->get('/about')
            ->assertStatus(404);
    }

    /** @test */
    function key_variables_key_added()
    {
        $page = $this->createPage('about');

        $response = $this->get('about')->assertStatus(200);

        $keys = [
            'site_url', 'homepage', 'current_url', 'current_uri', 'current_date', 'now', 'today', 'locale',
            'locale_name', 'locale_full', 'locale_url', 'get', 'post', 'get_post', 'old', 'response_code',
            'logged_in', 'logged_out', 'environment', 'xml_header', 'csrf_token', 'csrf_field', 'config',
        ];

        $cascade = $this->app['Statamic\View\Cascade']->toArray();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $cascade);
        }
    }

    /** @test */
    function fields_gets_augmented()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '{{ augment_me }}{{ dont_augment_me }}');
        Blueprint::shouldReceive('find')
            ->with('test')
            ->andReturn((new \Statamic\Fields\Blueprint)
                ->setHandle('test')
                ->setContents(['fields' => [
                    [
                        'handle' => 'augment_me',
                        'field' => ['type' => 'markdown']
                    ]
                ]]));

        $this->createPage('about', [
            'path' => 'about.md',
            'with' => [
                'blueprint' => 'test',
                'augment_me' => '# Foo *Bar*',
                'dont_augment_me' => '# Foo *Bar*',
            ]
        ]);

        $response = $this->get('about');

        $this->assertEquals("<h1>Foo <em>Bar</em></h1>\n# Foo *Bar*", trim($response->content()));
    }

    /** @test */
    function changes_content_type_to_xml()
    {
        $this->createPage('about', ['with' => ['content_type' => 'xml']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    /** @test */
    function changes_content_type_to_atom()
    {
        $this->createPage('about', ['with' => ['content_type' => 'atom']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /** @test */
    function changes_content_type_to_json()
    {
        $this->createPage('about', ['with' => ['content_type' => 'json']]);

        $this->get('about')->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    function changes_content_type_to_text()
    {
        $this->createPage('about', ['with' => ['content_type' => 'text']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** @test */
    function sends_powered_by_header_if_enabled()
    {
        config(['statamic.system.send_powered_by_header' => true]);
        $this->createPage('about');

        $this->get('about')->assertHeader('X-Powered-By', 'Statamic');
    }

    /** @test */
    function doesnt_send_powered_by_header_if_disabled()
    {
        config(['statamic.system.send_powered_by_header' => false]);
        $this->createPage('about');

        $this->get('about')->assertHeaderMissing('X-Powered-By', 'Statamic');
    }

    /** @test */
    function headers_can_be_set_in_content()
    {
        $page = $this->createPage('about', ['with' => [
            'headers' => [
                'X-Some-Header' => 'Foo',
                'X-Another-Header' => 'Bar'
            ]
        ]]);

        $this->get('about')
            ->assertHeader('X-Some-Header', 'Foo')
            ->assertHeader('X-Another-Header', 'Bar');
    }

    /** @test */
    function event_is_emitted_when_response_is_created()
    {
        Event::fake([ResponseCreated::class]);

        $this->createPage('about')->set('headers', ['X-Foo' => 'Bar'])->save();

        $this->get('about')->assertStatus(200);

        Event::assertDispatched(ResponseCreated::class, function ($event) {
            return $event->response instanceof Response
                && $event->response->headers->has('X-Foo');
        });
    }

    /** @test */
    function ignored_segments_are_removed_from_url()
    {
        $class = app(\Statamic\Http\Controllers\FrontendController::class);

        config(['statamic.routes.ignore' => ['bar', 'qux']]);

        $this->assertEquals('/foo', $class->removeIgnoredSegments('/foo'));
        $this->assertEquals('/foo', $class->removeIgnoredSegments('/foo/bar'));
        $this->assertEquals('/foo/baz', $class->removeIgnoredSegments('/foo/bar/baz'));
        $this->assertEquals('/foo/baz', $class->removeIgnoredSegments('/foo/bar/baz/qux'));
        $this->assertEquals('/foo/baz/flux', $class->removeIgnoredSegments('/foo/bar/baz/qux/flux'));
    }

    /** @test */
    function amp_requests_load_their_amp_directory_counterparts()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function amp_requests_without_an_amp_template_result_in_a_404()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function routes_pointing_to_controllers_should_render()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function routes_pointing_to_invalid_controller_should_render_404()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function a_redirect_key_in_the_page_data_should_redirect()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function a_redirect_key_with_a_404_value_should_404()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function debug_bar_shows_cascade_variables_if_enabled()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function the_404_page_is_treated_like_a_template()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', 'Not found {{ response_code }} {{ site }}');

        $this->get('unknown')->assertNotFound()->assertSee('Not found 404 en');

        // todo: test cascade vars are in the debugbar
    }

    /** @test */
    function it_sets_the_translation_locale_based_on_site()
    {
        app('translator')->addNamespace('test', __DIR__.'/__fixtures__/lang');

        Site::setConfig(['sites' => [
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<p>{{ trans key="test::messages.hello" }}</p>');

        $this->makeCollection()->save();
        tap($this->makePage('about', ['with' => ['template' => 'some_template']])->locale('english'))->save();
        tap($this->makePage('le-about', ['with' => ['template' => 'some_template']])->locale('french'))->save();

        $this->get('/about')->assertSee('Hello');
        $this->get('/fr/le-about')->assertSee('Bonjour');
    }

    private function createPage($slug, $attributes = [])
    {
        $this->makeCollection()->save();

        return tap($this->makePage($slug, $attributes))->save();
    }

    private function makePage($slug, $attributes = [])
    {
        return EntryFactory::slug($slug)
            ->id($slug)
            ->collection('pages')
            ->data($attributes['with'] ?? [])
            ->make();
    }

    private function makeCollection()
    {
        return Collection::make('pages')
            ->route('{slug}')
            ->template('default')
            ->entryBlueprints(['empty']);
    }
}
