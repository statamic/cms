<?php

namespace Tests;

use Statamic\API\User;
use Statamic\API\Page;
use Statamic\API\Role;
use Statamic\API\Site;
use Statamic\Stache\Stache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

class FrontendTest extends TestCase
{
    use FakesViews;

    public function setUp()
    {
        parent::setUp();

        $this->withStandardFakeViews();

        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => [
                    'name' => 'English',
                    'locale' => 'en_US',
                    'url' => 'http://localhost/',
                ]
            ]
        ]);
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
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> {{ content }}');

        $page = $this->createPage('/about', [
            'path' => 'pages/index.md',
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the *about* page.',
                'template' => 'some_template',
            ]
        ]);

        $response = $this->get('/about')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the <em>about</em> page.</p>', trim($response->content()));
    }

    /** @test */
    function page_is_displayed_with_query_string()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> {{ content }}');

        $page = $this->createPage('/about', [
            'path' => 'pages/index.md',
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the *about* page.',
                'template' => 'some_template',
            ]
        ]);

        $response = $this->get('/about?some=querystring')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the <em>about</em> page.</p>', trim($response->content()));
    }

    /** @test */
    function pages_get_protected()
    {
        $this->markTestIncomplete(); // need to implement whole site protection

        $page = $this->createPage('/');

        config(['statamic.system.protect' => [
            'type' => 'something'
        ]]);

        $this->get('/')->assertStatus(403);
    }

    /** @test */
    function drafts_are_not_visible()
    {
        $this->withStandardFakeErrorViews();
        $page = $this->createPage('/about');
        $page->published(false);

        $this->get('/about')->assertStatus(404);
    }

    /** @test */
    function drafts_are_visible_if_logged_in_with_correct_permission()
    {
        $this->fakeRoles(['draft_viewer' => ['permissions' => ['content:view_drafts_on_frontend']]]);
        $user = User::create()->with(['roles' => ['draft_viewer']])->get();

        $page = $this->createPage('/about');
        $page->published(false);
        $page->set('content', 'Testing 123');

        $response = $this
            ->actingAs($user)
            ->get('/about')
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
    function live_preview_overrides_data()
    {
        $this->markTestIncomplete(); // todo: live preview

        $this->fakeRoles(['cp_accessor' => ['permissions' => ['cp:access']]]);
        $user = User::create()->with(['roles' => ['cp_accessor']])->get();

        $page = $this->createPage('/about');
        $page->set('content', 'Testing 123');
        $page->set('fieldset', 'default');

        $this
            ->actingAs($user)
            ->post('/about', ['preview' => true, 'fields' => ['content' => 'Updated content']])
            ->assertStatus(200)
            ->assertSee('Updated content');
    }

    /** @test */
    function key_variables_key_added()
    {
        $page = $this->createPage('/');

        $response = $this->get('/')->assertStatus(200);

        $keys = [
            'site_url', 'homepage', 'current_url', 'current_uri', 'current_date', 'now', 'today', 'locale',
            'locale_name', 'locale_full', 'locale_url', 'get', 'post', 'get_post', 'old', 'response_code',
            'logged_in', 'logged_out', 'environment', 'xml_header', 'csrf_token', 'csrf_field', 'config',
        ];

        $cascade = $this->app['Statamic\Cascade']->toArray();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $cascade);
        }
    }

    /** @test */
    function only_content_gets_automatically_parsed_as_markdown()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '{{ content }}{{ subtitle }}');

        $this->createPage('/', [
            'path' => 'pages/index.md',
            'with' => [
                'content' => '# Foo *Bar*',
                'subtitle' => '# Foo *Bar*',
            ]
        ]);

        $response = $this->get('/');

        $this->assertEquals("<h1>Foo <em>Bar</em></h1>\n# Foo *Bar*", trim($response->content()));
    }

    /** @test */
    function content_gets_automatically_parsed_as_textile()
    {
        $this->viewShouldReturnRaw('default', '{{ content }}{{ subtitle }}');

        $this->createPage('/', [
            'path' => 'pages/index.textile',
            'with' => [
                'content' => 'h1. Foo *Bar*',
                'subtitle' => 'h1. Foo *Bar*',
            ]
        ]);

        $response = $this->get('/');

        $this->assertEquals("<h1>Foo <strong>Bar</strong></h1>h1. Foo *Bar*", trim($response->content()));
    }

    /** @test */
    function changes_content_type_to_xml()
    {
        $this->createPage('/', ['with' => ['content_type' => 'xml']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('/')->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    /** @test */
    function changes_content_type_to_atom()
    {
        $this->createPage('/', ['with' => ['content_type' => 'atom']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('/')->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /** @test */
    function changes_content_type_to_json()
    {
        $this->createPage('/', ['with' => ['content_type' => 'json']]);

        $this->get('/')->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    function changes_content_type_to_text()
    {
        $this->createPage('/', ['with' => ['content_type' => 'text']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('/')->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** @test */
    function sends_powered_by_header_if_enabled()
    {
        config(['statamic.system.send_powered_by_header' => true]);
        $this->createPage('/');

        $this->get('/')->assertHeader('X-Powered-By', 'Statamic');
    }

    /** @test */
    function doesnt_send_powered_by_header_if_disabled()
    {
        config(['statamic.system.send_powered_by_header' => false]);
        $this->createPage('/');

        $this->get('/')->assertHeaderMissing('X-Powered-By', 'Statamic');
    }

    /** @test */
    function headers_can_be_set_in_content()
    {
        $page = $this->createPage('/', ['with' => [
            'headers' => [
                'X-Some-Header' => 'Foo',
                'X-Another-Header' => 'Bar'
            ]
        ]]);

        $this->get('/')
            ->assertHeader('X-Some-Header', 'Foo')
            ->assertHeader('X-Another-Header', 'Bar');
    }

    /** @test */
    function event_is_emitted_when_response_is_created()
    {
        Event::fake();

        $page = $this->createPage('/');
        $page->set('headers', ['X-Foo' => 'Bar']);

        $this->get('/')->assertStatus(200);

        Event::assertDispatched('Statamic\Events\ResponseCreated', function ($event) {
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
        $this->markTestIncomplete();

        // all the key variables from the cascade are available
        // and they're in the debugbar
        // the 'response_code' key var is 404
    }

    private function createPage($url, $factoryAttributes = [])
    {
        $id = 'test-page-id';
        $path = $url . '/index.md';

        $page = Page::create($url)->id($id);

        foreach ($factoryAttributes as $attrKey => $attrValue) {
            $page->$attrKey($attrValue);
        }

        $page = $page->get();

        $stache = $this->app->make(Stache::class);
        $stache->repo('pages')->setPath($id, $path)->setUri($id, $url)->setItem($id, $page);
        $stache->repo('pagestructure')->setUri($id, $url)->setPath($id, $path)->setItem($id, $page->structure());
        return $page;
    }

    private function fakeRoles($roles)
    {
        $roles = collect($roles)->mapWithKeys(function ($role, $id) {
            return [$id => app('Statamic\Contracts\Permissions\RoleFactory')->create($role, $id)];
        });

        Role::shouldReceive('all')->andReturn($roles);
    }
}