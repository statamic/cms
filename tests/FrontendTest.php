<?php

namespace Tests;

use Facades\Statamic\CP\LivePreview;
use Facades\Statamic\Routing\ResolveRedirect;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\ResponseCreated;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Cascade;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class FrontendTest extends TestCase
{
    use FakesContent;
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
        $this->addToAssertionCount(-1);
        Blueprint::shouldReceive('in')->withAnyArgs()->zeroOrMoreTimes()->andReturn(collect([new \Statamic\Fields\Blueprint]));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function page_is_displayed_with_ending_slash()
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

        $response = $this->get('/about/')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
    }

    #[Test]
    public function page_is_displayed_with_query_string_and_ending_slash()
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

        $response = $this->get('/about/?some=querystring')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
    }

    #[Test]
    public function page_with_no_explicit_layout_will_not_use_a_layout()
    {
        $this->withStandardBlueprints();
        $this->withoutExceptionHandling();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', 'Layout {{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<h1>{{ title }}</h1> <p>{{ content }}</p>');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'template' => 'some_template',
                'layout' => false,
            ],
        ]);

        $response = $this->get('/about')->assertStatus(200);

        $this->assertEquals('<h1>The About Page</h1> <p>This is the about page.</p>', trim($response->content()));
        $response->assertDontSee('Layout');
    }

    #[Test]
    public function home_page_on_second_subdirectory_based_site_is_displayed()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->createHomePagesForTwoSites();

        $response = $this->get('/fr')->assertStatus(200);

        $this->assertEquals('French Home', trim($response->content()));
    }

    #[Test]
    public function home_page_on_second_subdirectory_based_site_is_displayed_with_ending_slash()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->createHomePagesForTwoSites();

        $response = $this->get('/fr/')->assertStatus(200);

        $this->assertEquals('French Home', trim($response->content()));
    }

    #[Test]
    public function home_page_on_second_domain_site_is_displayed()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://anotherhost.com/', 'locale' => 'fr'],
        ]);

        $this->createHomePagesForTwoSites();

        $response = $this->get('http://anotherhost.com')->assertStatus(200);

        $this->assertEquals('French Home', trim($response->content()));
    }

    #[Test]
    public function home_page_on_second_domain_site_is_displayed_with_ending_slash()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://anotherhost.com/', 'locale' => 'fr'],
        ]);

        $this->createHomePagesForTwoSites();

        $response = $this->get('http://anotherhost.com/')->assertStatus(200);

        $this->assertEquals('French Home', trim($response->content()));
    }

    private function createHomePagesForTwoSites()
    {
        $this->withStandardBlueprints();
        $this->withoutExceptionHandling();
        $this->withStandardFakeViews();

        $c = tap(Collection::make('pages')->sites(['english', 'french'])->routes('{slug}')->structureContents(['root' => true]))->save();

        EntryFactory::id('1')->locale('english')->slug('home')->collection('pages')->data(['content' => 'Home'])->create();
        EntryFactory::id('2')->locale('french')->slug('french-home')->collection('pages')->data(['content' => 'French Home'])->create();

        $c->structure()->in('english')->tree([['entry' => '1']])->save();
        $c->structure()->in('french')->tree([['entry' => '2']])->save();
    }

    #[Test]
    public function drafts_are_not_visible()
    {
        $this->withStandardFakeErrorViews();
        $this->createPage('about')->published(false)->save();

        $this->get('/about')->assertStatus(404);
    }

    #[Test]
    public function drafts_are_visible_if_using_live_preview()
    {
        $this->withStandardBlueprints();

        $page = tap($this->createPage('about')->published(false)->set('content', 'Testing 123'))->save();

        LivePreview::tokenize('test-token', $page);

        $response = $this
            ->get('/about?token=test-token')
            ->assertStatus(200)
            ->assertHeader('X-Statamic-Draft', true);

        $this->assertEquals('Testing 123', $response->content());
    }

    #[Test]
    public function drafts_dont_get_statically_cached()
    {
        $this->markTestIncomplete();
    }

    #[Test]
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

    #[Test]
    public function future_private_entries_viewable_in_live_preview()
    {
        Carbon::setTestNow(Carbon::parse('2019-01-01'));
        $this->withStandardFakeErrorViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRendered('default', 'The template contents');

        tap($this->makeCollection()->dated(true)->futureDateBehavior('private'))->save();
        $page = tap($this->makePage('about')->date('2019-01-02'))->save();

        LivePreview::tokenize('test-token', $page);

        $this
            ->get('/about?token=test-token')
            ->assertStatus(200)
            ->assertHeader('X-Statamic-Private', true);
    }

    #[Test]
    public function future_private_entries_dont_get_statically_cached()
    {
        $this->markTestIncomplete();
    }

    #[Test]
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

    #[Test]
    public function past_private_entries_are_viewable_in_live_preview()
    {
        Carbon::setTestNow(Carbon::parse('2019-01-01'));
        $this->withStandardFakeErrorViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRendered('default', 'The template contents');

        tap($this->makeCollection()->dated(true)->pastDateBehavior('private'))->save();
        $page = tap($this->makePage('about')->date('2018-01-01'))->save();

        LivePreview::tokenize('test-token', $page);

        $this
            ->get('/about?token=test-token')
            ->assertStatus(200)
            ->assertHeader('X-Statamic-Private', true);
    }

    #[Test]
    public function past_private_entries_dont_get_statically_cached()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function header_is_added_to_protected_responses()
    {
        $page = $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertHeaderMissing('X-Statamic-Protected');

        $page->set('protect', 'logged_in')->save();

        $this
            ->actingAs(User::make())
            ->get('/about')
            ->assertOk()
            ->assertHeader('X-Statamic-Protected', true);
    }

    #[Test]
    public function key_variables_key_added()
    {
        $page = $this->createPage('about');

        $response = $this->get('about')->assertStatus(200);

        $keys = [
            'site', 'homepage', 'current_url', 'current_uri', 'current_date', 'now', 'today',
            'get', 'post', 'get_post', 'old', 'response_code',
            'logged_in', 'logged_out', 'current_user', 'environment', 'xml_header', 'csrf_token', 'csrf_field', 'config',
        ];

        $cascade = $this->app['Statamic\View\Cascade']->toArray();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $cascade);
        }
    }

    #[Test]
    public function fields_gets_augmented()
    {
        $this->withoutExceptionHandling();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '{{ augment_me }}{{ dont_augment_me }}');
        $blueprint = Blueprint::makeFromFields([
            'augment_me' => ['type' => 'markdown'],
        ])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/pages')->once()->andReturn(collect([$blueprint]));

        $this->createPage('about', [
            'path' => 'about.md',
            'with' => [
                'blueprint' => 'test',
                'augment_me' => '# Foo *Bar*',
                'dont_augment_me' => '# Foo *Bar*',
            ],
        ]);

        $response = $this->get('about');

        $this->assertEquals("<h1>Foo <em>Bar</em></h1>\n# Foo *Bar*", StringUtilities::normalizeLineEndings(trim($response->content())));
    }

    #[Test]
    public function changes_content_type_to_xml()
    {
        $this->createPage('about', ['with' => ['content_type' => 'xml']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    #[Test]
    public function changes_content_type_to_atom()
    {
        $this->createPage('about', ['with' => ['content_type' => 'atom']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    #[Test]
    public function changes_content_type_to_json()
    {
        $this->createPage('about', ['with' => ['content_type' => 'json']]);

        $this->get('about')->assertHeader('Content-Type', 'application/json');
    }

    #[Test]
    public function changes_content_type_to_text()
    {
        $this->createPage('about', ['with' => ['content_type' => 'text']]);

        // Laravel adds utf-8 if the content-type starts with text/
        $this->get('about')->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    #[Test]
    public function xml_antlers_template_with_xml_layout_will_use_both_and_change_the_content_type()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');
        $this->createPage('about', ['with' => ['template' => 'feed']]);

        $response = $this
            ->get('about')
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $this->assertEquals('<?xml ?><foo></foo>', $response->getContent());
    }

    #[Test]
    public function xml_antlers_template_with_non_xml_layout_will_change_content_type_but_avoid_using_the_layout()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html>{{ template_content }}</html>', 'antlers.html');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');
        $this->createPage('about', ['with' => ['template' => 'feed']]);

        $response = $this
            ->get('about')
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $this->assertEquals('<foo></foo>', $response->getContent());
    }

    #[Test]
    public function xml_antlers_layout_will_change_the_content_type()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.html');
        $this->createPage('about', ['with' => ['template' => 'feed']]);

        $response = $this
            ->get('about')
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
        $this->createPage('about', ['with' => ['template' => 'feed']]);

        $response = $this
            ->get('about')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $this->assertEquals('<foo></foo>', $response->getContent());
    }

    #[Test]
    public function xml_template_with_custom_content_type_does_not_change_to_xml()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<?xml ?>{{ template_content }}', 'antlers.xml');
        $this->viewShouldReturnRaw('feed', '<foo></foo>', 'antlers.xml');
        $this->createPage('about', ['with' => ['template' => 'feed', 'content_type' => 'json']]);

        $this
            ->get('about')
            ->assertHeader('Content-Type', 'application/json');
    }

    #[Test]
    public function sends_powered_by_header_if_enabled()
    {
        config(['statamic.system.send_powered_by_header' => true]);
        $this->createPage('about');

        $this->get('about')->assertHeader('X-Powered-By', 'Statamic');
    }

    #[Test]
    public function doesnt_send_powered_by_header_if_disabled()
    {
        config(['statamic.system.send_powered_by_header' => false]);
        $this->createPage('about');

        $this->get('about')->assertHeaderMissing('X-Powered-By', 'Statamic');
    }

    #[Test]
    public function disables_floc_through_header_by_default()
    {
        $this->createPage('about');

        $this->get('about')->assertHeader('Permissions-Policy', 'interest-cohort=()');
    }

    #[Test]
    public function doesnt_disable_floc_through_header_if_disabled()
    {
        config(['statamic.system.disable_floc' => false]);
        $this->createPage('about');

        $this->get('about')->assertHeaderMissing('Permissions-Policy', 'interest-cohort=()');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function amp_requests_load_their_amp_directory_counterparts()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function amp_requests_without_an_amp_template_result_in_a_404()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function routes_pointing_to_controllers_should_render()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function routes_pointing_to_invalid_controller_should_render_404()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function a_redirect_key_in_the_page_data_should_redirect()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function a_redirect_key_with_a_404_value_should_404()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function a_redirect_key_with_an_entry_should_redirect_to_the_entry()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function a_redirect_key_with_an_unknown_entry_should_404()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function debug_bar_shows_cascade_variables_if_enabled()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function the_404_page_is_treated_like_a_template()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', 'Not found {{ response_code }} {{ site:handle }}');

        $this->get('unknown')->assertNotFound()->assertSee('Not found 404 en');

        $this->assertEquals(404, Cascade::get('response_code'));

        // todo: test cascade vars are in the debugbar
    }

    #[Test]
    public function it_sets_the_translation_locale_based_on_site()
    {
        app('translator')->addNamespace('test', __DIR__.'/__fixtures__/lang');

        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<p>{{ trans key="test::messages.hello" }}</p>');

        $this->makeCollection()->sites(['english', 'french'])->save();
        tap($this->makePage('about', ['with' => ['template' => 'some_template']])->locale('english'))->save();
        tap($this->makePage('le-about', ['with' => ['template' => 'some_template']])->locale('french'))->save();

        $this->get('/about')->assertSee('Hello');
        $this->get('/fr/le-about')->assertSee('Bonjour');
    }

    #[Test]
    public function it_sets_the_carbon_to_string_format()
    {
        config(['statamic.system.date_format' => 'd/m/Y']);
        Date::setTestNow('October 21st, 2022');
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', '<p>{{ now }}</p>');
        $this->makeCollection()->save();
        tap($this->makePage('about', ['with' => ['template' => 'some_template']]))->save();

        $this->assertDefaultCarbonFormat();

        $this->get('/about')->assertSee('21/10/2022');

        $this->assertDefaultCarbonFormat();
    }

    #[Test]
    public function it_sets_the_locale()
    {
        // You can only set the locale to one that is actually installed on the server.
        // The names are a little different across jobs in the GitHub actions matrix.
        // We'll test against whichever was successfully applied. Finally, we will
        // reset the locale back to the original state to start the test clean.
        $locales = ['fr_FR', 'fr_FR.utf-8', 'fr_FR.UTF-8', 'french'];
        $originalLocale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, $locales);
        $frLocale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, $originalLocale);

        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en', 'lang' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => $frLocale, 'lang' => 'fr'],
        ]);

        (new class extends Tags
        {
            public static $handle = 'php_locale';

            public function index()
            {
                return setlocale(LC_TIME, 0);
            }
        })->register();

        (new class extends Tags
        {
            public static $handle = 'laravel_locale';

            public function index()
            {
                return app()->getLocale();
            }
        })->register();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', 'PHP Locale: {{ php_locale }} App Locale: {{ laravel_locale }}');

        $this->makeCollection()->sites(['english', 'french'])->save();
        tap($this->makePage('about', ['with' => ['template' => 'some_template']])->locale('english'))->save();
        tap($this->makePage('le-about', ['with' => ['template' => 'some_template']])->locale('french'))->save();

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals($originalLocale, setlocale(LC_TIME, 0));

        $this->get('/fr/le-about')->assertSeeInOrder([
            'PHP Locale: '.$frLocale,
            'App Locale: fr',
        ]);

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals($originalLocale, setlocale(LC_TIME, 0));
    }

    private function assertDefaultCarbonFormat()
    {
        $this->assertEquals(
            Date::now()->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            (string) Date::now(),
            'Carbon was not formatted using the default format.'
        );
    }

    /**
     * @see https://github.com/statamic/cms/issues/1537
     **/
    #[Test]
    public function home_page_is_not_overridden_by_entries_in_another_structured_collection_with_no_url()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1>');

        // The bug would happen if the non-routable collection happened to be created first. It's not
        // really specific to the naming. However when reading from files, it goes in alphabetical
        // order which makes it seem like it could be an alphabetical problem.
        $c = tap(Collection::make('services')->structureContents(['root' => true]))->save();
        $c->structure()->in('en')->tree([['entry' => '2']])->save();

        $c = tap(Collection::make('pages')->routes('{slug}')->structureContents(['root' => true]))->save();
        $c->structure()->in('en')->tree([['entry' => '1']])->save();

        EntryFactory::id('1')->slug('service')->collection('services')->data(['title' => 'Service'])->create();
        EntryFactory::id('2')->slug('home')->collection('pages')->data(['title' => 'Home'])->create();

        // Before the fix, you'd see "Service" instead of "Home", because the URI would also be /
        $this->get('/')->assertSee('Home');
    }

    #[Test]
    #[DataProvider('redirectProvider')]
    public function redirect_is_followed($dataValue, $augmentedValue, $expectedStatus, $expectedLocation)
    {
        // Making a fake fieldtype to test that the augmented value is used for the redirect.
        // The actual redirect resolving logic is already completely under test, and happens
        // in the "link" fieldtype's augment method.

        app()->bind('test-augmented-value', fn () => $augmentedValue);

        (new class($augmentedValue) extends \Statamic\Fields\Fieldtype
        {
            protected static $handle = 'fake_link';

            public function augment($value)
            {
                return app('test-augmented-value');
            }
        })->register();

        $blueprint = Blueprint::makeFromFields(['redirect' => ['type' => 'fake_link']]);
        Blueprint::shouldReceive('in')->with('collections/pages')->andReturn(collect([$blueprint]));

        $this->createPage('about', [
            'with' => [
                'title' => 'About',
                'redirect' => $dataValue, // this should not be used - the augmented value should.
            ],
        ])->save();

        $response = $this->get('/about');

        if ($expectedStatus === 302) {
            $response->assertRedirect($expectedLocation);
        } elseif ($expectedStatus === 200) {
            $response->assertOk();
        } elseif ($expectedStatus === 404) {
            $response->assertNotFound();
        } else {
            throw new \Exception('Test not set up to handle status code: '.$expectedStatus);
        }
    }

    public static function redirectProvider()
    {
        return [
            'valid redirect' => [
                '/shouldnt-be-used',   // its got a value
                '/target',             // the fieldtype will augment to this
                302,                   // its a redirect
                '/target',             // to here
            ],
            'invalid redirect' => [
                'something',           // its got a value
                null,                  // the fieldtype will augment to this because its an invalid reference
                404,                   // so it should 404
                null,                  // and not redirect
            ],
            'missing redirect' => [
                null,                  // its got no value
                null,                  // the fieldtype will augment to this (although it wouldn't even be called)
                200,                   // since there's no redirect, its a successful response
                null,                  // and not a redirect
            ],
        ];
    }

    #[Test]
    #[DataProvider('redirectProviderNoBlueprintProvider')]
    public function redirect_is_followed_when_no_field_is_present_in_blueprint(
        $dataValue,
        $shouldResolve,
        $resolvedValue,
        $expectedStatus,
        $expectedLocation
    ) {
        $entry = tap($this->createPage('about', [
            'with' => [
                'title' => 'About',
                'redirect' => $dataValue,
            ],
        ]))->save();

        $mock = ResolveRedirect::shouldReceive('resolve');

        if ($shouldResolve) {
            $mock->with($dataValue, $entry)->andReturn($resolvedValue)->once();
        } else {
            $mock->never();
        }

        $response = $this->get('/about');

        if ($expectedStatus === 302) {
            $response->assertRedirect($expectedLocation);
        } elseif ($expectedStatus === 200) {
            $response->assertOk();
        } elseif ($expectedStatus === 404) {
            $response->assertNotFound();
        } else {
            throw new \Exception('Test not set up to handle status code: '.$expectedStatus);
        }
    }

    public static function redirectProviderNoBlueprintProvider()
    {
        return [
            'valid redirect' => [
                // A valid redirect could be a literal URL, "@child", "entry::id", etc.
                // It's irrelevant for this test since we're mocking the resolver.
                'something',           // the value
                true,                  // the resolver will run, getting the above value
                '/target',             // and return this.
                302,                   // its a redirect
                '/target',             // to here.
            ],
            'missing redirect' => [
                null,                  // its got no value
                false,                 // so the resolver will not be called at all.
                null,                  // irrelevant since it won't be called.
                200,                   // since there's no redirect, its a successful response
                null,                  // and not a redirect
            ],
            'intentional 404' => [
                '404',                 // its got a value
                true,                  // the resolver will run, getting the above value,
                404,                   // and return this
                404,                   // so it should 404
                null,                  // and not redirect
            ],
        ];
    }

    #[Test]
    public function redirect_is_followed_when_value_is_inherited_from_origin()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $blueprint = Blueprint::makeFromFields([
            'redirect' => [
                'type' => 'group',
                'fields' => [
                    ['handle' => 'url', 'field' => ['type' => 'link']],
                    ['handle' => 'status', 'field' => ['type' => 'radio', 'options' => [301, 302]]],
                ],
            ]]);
        Blueprint::shouldReceive('in')->with('collections/pages')->andReturn(collect([$blueprint]));

        Collection::make('pages')->sites(['en', 'fr'])->routes(['en' => '{slug}', 'fr' => '{slug}'])->save();

        $entry = tap($this->createPage('about', [
            'with' => [
                'title' => 'About',
                'redirect' => [
                    'url' => '/test',
                    'status' => 301,
                ],
            ],
        ]))->save();
        tap($entry->makeLocalization('fr'))->save();

        $response = $this->get('/fr/about');

        $response->assertRedirect('/test');
        $response->assertStatus(301);
    }

    #[Test]
    public function redirect_http_status_is_applied_when_present_in_blueprint()
    {
        $blueprint = Blueprint::makeFromFields([
            'redirect' => [
                'type' => 'group',
                'fields' => [
                    ['handle' => 'url', 'field' => ['type' => 'link']],
                    ['handle' => 'status', 'field' => ['type' => 'radio', 'options' => [301, 302]]],
                ],
            ]]);
        Blueprint::shouldReceive('in')->with('collections/pages')->andReturn(collect([$blueprint]));

        tap($this->createPage('about', [
            'with' => [
                'title' => 'About',
                'redirect' => [
                    'url' => '/test',
                    'status' => 301,
                ],
            ],
        ]))->save();

        $response = $this->get('/about');

        $response->assertRedirect('/test');
        $response->assertStatus(301);
    }

    #[Test]
    public function redirect_http_status_is_applied_when_missing_from_blueprint()
    {
        tap($this->createPage('about', [
            'with' => [
                'title' => 'About',
                'redirect' => [
                    'url' => '/test',
                    'status' => 301,
                ],
            ],
        ]))->save();

        $response = $this->get('/about');

        $response->assertRedirect('/test');
        $response->assertStatus(301);
    }

    #[Test]
    public function it_protects_404_pages()
    {
        $this->get('/does-not-exist')->assertStatus(404);

        config(['statamic.protect.default' => 'logged_in']);

        $this->get('/does-not-exist')->assertRedirect('/login?redirect=http://localhost/does-not-exist');

        $this
            ->actingAs(User::make())
            ->get('/does-not-exist')
            ->assertStatus(404);
    }
}
