<?php

namespace Tests;

use Mockery;
use Statamic\API\File;
use Statamic\API\User;
use Statamic\API\Page;
use Statamic\API\Role;
use Statamic\API\Taxonomy;
use Statamic\Stache\Stache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Statamic\Filesystem\FilesystemAdapter;

class FrontendTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['statamic.sites' => [
            'default' => 'en',
            'sites' => [
                'en' => [
                    'url' => 'http://localhost/',
                ]
            ]
        ]]);
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
    function pages_get_protected()
    {
        $page = $this->createPage('/');

        config(['statamic.system.protect' => [
            'type' => 'something'
        ]]);

        $this->get('/')->assertStatus(403);
    }

    /** @test */
    function drafts_are_not_visible()
    {
        $this->fakeTemplate();
        $this->fakeErrorTemplate();

        $page = $this->createPage('/about');
        $page->published(false);
        $this->mockPageToArray(['path' => 'pages/_about/index.md']);

        $this->get('/about')->assertStatus(404);
    }

    /** @test */
    function drafts_are_visible_if_logged_in_with_correct_permission()
    {
        $this->fakeTemplate();
        $this->fakeRoles(['draft_viewer' => ['permissions' => ['content:view_drafts_on_frontend']]]);
        $user = User::create()->with(['roles' => ['draft_viewer']])->get();

        $page = $this->createPage('/about');
        $page->published(false);
        $page->set('content', 'Testing 123');
        $this->mockPageToArray(['path' => 'pages/_about/index.md']);

        $this
            ->actingAs($user)
            ->get('/about')
            ->assertStatus(200)
            ->assertSee('Testing 123');
    }

    /** @test */
    function drafts_dont_get_statically_cached()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function live_preview_overrides_data()
    {
        $this->fakeTemplate();
        $this->fakeRoles(['cp_accessor' => ['permissions' => ['cp:access']]]);
        $user = User::create()->with(['roles' => ['cp_accessor']])->get();

        $page = $this->createPage('/about');
        $page->set('content', 'Testing 123');
        $page->set('fieldset', 'default');
        $this->mockPageToArray(['path' => 'pages/about/index.md']);

        File::shouldReceive('exists')->with($fieldsetPath = 'resources/fieldsets/default.yaml')->andReturnTrue();
        File::shouldReceive('get')->with($fieldsetPath)->andReturn("fields:\n  content:\n    type: text");

        $this
            ->actingAs($user)
            ->post('/about', ['preview' => true, 'fields' => ['content' => 'Updated content']])
            ->assertStatus(200)
            ->assertSee('Updated content');
    }

    /** @test */
    function key_variables_key_added()
    {
        $this->withoutExceptionHandling();
        $this->fakeTemplate();

        $page = $this->createPage('/');
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $this->get('/')->assertStatus(200);

        $keys = [
            'site_url', 'homepage', 'current_url', 'current_uri', 'current_date', 'now', 'today', 'locale',
            'locale_name', 'locale_full', 'locale_url', 'get', 'post', 'get_post', 'old', 'response_code',
            'logged_in', 'logged_out', 'environment', 'xml_header', 'csrf_token', 'csrf_field', 'settings',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, datastore()->getScope('cascade'));
        }
    }

    /** @test */
    function changes_content_type_to_xml()
    {
        $this->fakeTemplate();
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $page = $this->createPage('/');
        $page->set('content_type', 'xml');

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('/')->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    /** @test */
    function changes_content_type_to_atom()
    {
        $this->fakeTemplate();
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $page = $this->createPage('/');
        $page->set('content_type', 'atom');

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('/')->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /** @test */
    function changes_content_type_to_json()
    {
        $this->fakeTemplate();
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $page = $this->createPage('/');
        $page->set('content_type', 'json');

        $this->get('/')->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    function changes_content_type_to_text()
    {
        $this->fakeTemplate();
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $page = $this->createPage('/');
        $page->set('content_type', 'text');

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('/')->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** @test */
    function sends_powered_by_header_if_enabled()
    {
        $this->fakeTemplate();
        config(['statamic.system.send_powered_by_header' => true]);

        $page = $this->createPage('/');
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $this->get('/')->assertHeader('X-Powered-By', 'Statamic');
    }

    /** @test */
    function doesnt_send_powered_by_header_if_disabled()
    {
        $this->fakeTemplate();
        config(['statamic.system.send_powered_by_header' => false]);

        $page = $this->createPage('/');
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $this->get('/')->assertHeaderMissing('X-Powered-By', 'Statamic');
    }

    /** @test */
    function headers_can_be_set_in_content()
    {
        $this->fakeTemplate();
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $page = $this->createPage('/');
        $page->set('headers', [
            'X-Some-Header' => 'Foo',
            'X-Another-Header' => 'Bar'
        ]);

        $this->get('/')
            ->assertHeader('X-Some-Header', 'Foo')
            ->assertHeader('X-Another-Header', 'Bar');
    }

    /** @test */
    function event_is_emitted_when_response_is_created()
    {
        Event::fake();
        $this->fakeTemplate();
        $this->mockPageToArray(['path' => 'pages/index.md']);

        $page = $this->createPage('/');
        $page->set('headers', ['X-Foo' => 'Bar']);

        $this->get('/')->assertStatus(200);

        Event::assertDispatched('response.created', function ($event, $response) {
            return $response instanceof Response
                && $response->headers->has('X-Foo');
        });
    }

    private function createPage($url)
    {
        $id = 'test-page-id';
        $path = $url . '/index.md';
        $page = Page::create($url)->id($id)->get();
        $stache = $this->app->make(Stache::class);
        $stache->repo('pages')->setPath($id, $path)->setUri($id, $url)->setItem($id, $page);
        $stache->repo('pagestructure')->setUri($id, $url)->setPath($id, $path)->setItem($id, $page->structure());
        return $page;
    }

    private function fakeTemplate($template = 'default', $content = '{{ content }}')
    {
        Taxonomy::shouldReceive('all')->andReturn(collect());
        File::shouldReceive('exists')->with($layout = resource_path('views/layout.antlers.html'))->andReturnTrue();
        File::shouldReceive('get')->with($layout)->andReturn('{{ template_content }}');
        File::shouldReceive('get')->with(resource_path("views/{$template}.antlers.html"))->andReturn($content);
    }

    private function fakeErrorTemplate($code = '404')
    {
        File::shouldReceive('exists')->with(resource_path('views/errors/layout.antlers.html'))->andReturnFalse();
        File::shouldReceive('get')->with(resource_path("views/errors/{$code}.antlers.html"))->andReturn('The error template.');
    }

    private function fakeRoles($roles)
    {
        $roles = collect($roles)->mapWithKeys(function ($role, $id) {
            return [$id => app('Statamic\Contracts\Permissions\RoleFactory')->create($role, $id)];
        });

        Role::shouldReceive('all')->andReturn($roles);
    }

    /**
     * When a page is loaded, it converts the content to an array.
     * Some operations in this will need to access the filesystem. For example, getting the last modified time.
     */
    private function mockPageToArray(array $attrs)
    {
        File::shouldReceive('disk')->with('content')->andReturn($fs = Mockery::mock(FilesystemAdapter::class));
        $fs->shouldReceive('lastModified')->with($attrs['path'])->andReturn(time());
    }
}