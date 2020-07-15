<?php

namespace Tests;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Statamic\Events\ResponseCreated;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class FrontendTest extends TestCase
{
    use FakesRoles;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withStandardFakeViews();
    }

    private function withStandardBlueprints()
    {
        Blueprint::shouldReceive('in')->withAnyArgs()->andReturn(collect([new \Statamic\Fields\Blueprint]));
        $this->addToAssertionCount(-1);
    }

    /** @test */
    public function page_is_displayed()
    {
        $this->withStandardBlueprints();
        $this->withoutExceptionHandling();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> <p>{{ content }}</p>');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'template' => 'some_template',
            ],
        ]);

        $response = $this->get('/about')
            ->assertStatus(200)
            ->assertHeaderMissing('X-Statamic-Draft');

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
    }

    /** @test */
    public function page_is_displayed_with_query_string()
    {
        $this->withStandardBlueprints();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> <p>{{ content }}</p>');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'template' => 'some_template',
            ],
        ]);

        $response = $this->get('/about?some=querystring')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
    }

    /** @test */
    public function drafts_are_not_visible()
    {
        $this->withStandardFakeErrorViews();
        $this->createPage('about')->published(false)->save();

        $this->get('/about')->assertStatus(404);
    }

    /** @test */
    public function drafts_are_visible_if_using_live_preview()
    {
        $this->withStandardBlueprints();
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
    public function drafts_dont_get_statically_cached()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function future_private_entries_are_not_viewable()
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
    public function past_private_entries_are_not_viewable()
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
    public function key_variables_key_added()
    {
        $page = $this->createPage('about');

        $response = $this->get('about')->assertStatus(200);

        $keys = [
            'site', 'homepage', 'current_url', 'current_uri', 'current_date', 'now', 'today',
            'get', 'post', 'get_post', 'old', 'response_code',
            'logged_in', 'logged_out', 'environment', 'xml_header', 'csrf_token', 'csrf_field', 'config',
        ];

        $cascade = $this->app['Statamic\View\Cascade']->toArray();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $cascade);
        }
    }

    /** @test */
    public function fields_gets_augmented()
    {
        $this->withoutExceptionHandling();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '{{ augment_me }}{{ dont_augment_me }}');
        Blueprint::shouldReceive('in')
            ->with('collections/pages')
            ->once()
            ->andReturn(collect([(new \Statamic\Fields\Blueprint)
                ->setHandle('test')
                ->setContents(['fields' => [
                    [
                        'handle' => 'augment_me',
                        'field' => ['type' => 'markdown'],
                    ],
                ]]), ]));

        $this->createPage('about', [
            'path' => 'about.md',
            'with' => [
                'blueprint' => 'test',
                'augment_me' => '# Foo *Bar*',
                'dont_augment_me' => '# Foo *Bar*',
            ],
        ]);

        $response = $this->get('about');

        $this->assertEquals("<h1>Foo <em>Bar</em></h1>\n# Foo *Bar*", trim($response->content()));
    }

    /** @test */
    public function changes_content_type_to_xml()
    {
        $this->createPage('about', ['with' => ['content_type' => 'xml']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    /** @test */
    public function changes_content_type_to_atom()
    {
        $this->createPage('about', ['with' => ['content_type' => 'atom']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /** @test */
    public function changes_content_type_to_json()
    {
        $this->createPage('about', ['with' => ['content_type' => 'json']]);

        $this->get('about')->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function changes_content_type_to_text()
    {
        $this->createPage('about', ['with' => ['content_type' => 'text']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** @test */
    public function sends_powered_by_header_if_enabled()
    {
        config(['statamic.system.send_powered_by_header' => true]);
        $this->createPage('about');

        $this->get('about')->assertHeader('X-Powered-By', 'Statamic');
    }

    /** @test */
    public function doesnt_send_powered_by_header_if_disabled()
    {
        config(['statamic.system.send_powered_by_header' => false]);
        $this->createPage('about');

        $this->get('about')->assertHeaderMissing('X-Powered-By', 'Statamic');
    }

    /** @test */
    public function headers_can_be_set_in_content()
    {
        $page = $this->createPage('about', ['with' => [
            'headers' => [
                'X-Some-Header' => 'Foo',
                'X-Another-Header' => 'Bar',
            ],
        ]]);

        $this->get('about')
            ->assertHeader('X-Some-Header', 'Foo')
            ->assertHeader('X-Another-Header', 'Bar');
    }

    /** @test */
    public function event_is_emitted_when_response_is_created()
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
    public function amp_requests_load_their_amp_directory_counterparts()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function amp_requests_without_an_amp_template_result_in_a_404()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function routes_pointing_to_controllers_should_render()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function routes_pointing_to_invalid_controller_should_render_404()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function a_redirect_key_in_the_page_data_should_redirect()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function a_redirect_key_with_a_404_value_should_404()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function debug_bar_shows_cascade_variables_if_enabled()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function the_404_page_is_treated_like_a_template()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', 'Not found {{ response_code }} {{ site:handle }}');

        $this->get('unknown')->assertNotFound()->assertSee('Not found 404 en');

        // todo: test cascade vars are in the debugbar
    }

    /** @test */
    public function it_sets_the_translation_locale_based_on_site()
    {
        app('translator')->addNamespace('test', __DIR__.'/__fixtures__/lang');

        Site::setConfig(['sites' => [
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<p>{{ trans key="test::messages.hello" }}</p>');

        $this->makeCollection()->sites(['english', 'french'])->save();
        tap($this->makePage('about', ['with' => ['template' => 'some_template']])->locale('english'))->save();
        tap($this->makePage('le-about', ['with' => ['template' => 'some_template']])->locale('french'))->save();

        $this->get('/about')->assertSee('Hello');
        $this->get('/fr/le-about')->assertSee('Bonjour');
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/issues/1537
     **/
    public function home_page_is_not_overridden_by_entries_in_another_structured_collection_with_no_url()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1>');

        // The bug would happen if the non-routable collection happened to be created first. It's not
        // really specific to the naming. However when reading from files, it goes in alphabetical
        // order which makes it seem like it could be an alphabetical problem.
        Collection::make('services')->structureContents([
            'root' => true,
            'tree' => [['entry' => '2']],
        ])->save();

        Collection::make('pages')->routes('{slug}')->structureContents([
            'root' => true,
            'tree' => [['entry' => '1']],
        ])->save();

        EntryFactory::id('1')->slug('service')->collection('services')->data(['title' => 'Service'])->create();
        EntryFactory::id('2')->slug('home')->collection('pages')->data(['title' => 'Home'])->create();

        // Before the fix, you'd see "Service" instead of "Home", because the URI would also be /
        $this->get('/')->assertSee('Home');
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
            ->routes('{slug}')
            ->template('default');
    }
}
