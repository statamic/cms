<?php

namespace Tests\View;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\View\Cascade;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CascadeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $cascade;

    public function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteConfig();
    }

    private function cascade()
    {
        if ($this->cascade) {
            return $this->cascade;
        }

        return $this->cascade = new Cascade(request(), new FakeSite);
    }

    /** @test */
    public function it_gets_the_instance()
    {
        $this->assertEquals($this->cascade(), $this->cascade()->instance());
    }

    /** @test */
    public function it_sets_and_gets_the_entire_cascade()
    {
        $this->assertEquals([], $this->cascade()->toArray());

        $this->cascade()->data(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->cascade()->toArray());
    }

    /** @test */
    public function it_gets_values()
    {
        $this->cascade()->data(['foo' => 'bar']);

        $this->assertEquals('bar', $this->cascade()->get('foo'));
    }

    /** @test */
    public function it_sets_values()
    {
        $this->assertEquals([], $this->cascade()->toArray());

        $this->cascade()->set('foo', 'bar');

        $this->assertEquals(
            ['foo' => 'bar'],
            $this->cascade()->toArray()
        );
    }

    /** @test */
    public function it_hydrates_constants()
    {
        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals(app()->environment(), $cascade['environment']);
            $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>', $cascade['xml_header']);
            $this->assertEquals(csrf_token(), $cascade['csrf_token']);
            $this->assertEquals(csrf_field(), $cascade['csrf_field']);
            $this->assertEquals(config()->all(), $cascade['config']);

            // Response code is constant. It gets manually overridden on errors.
            $this->assertEquals(200, $cascade['response_code']);
        });
    }

    /** @test */
    public function it_hydrates_auth_when_logged_in()
    {
        $this->actingAs(User::make())->get('/');

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $this->assertTrue($cascade['logged_in']);
            $this->assertFalse($cascade['logged_out']);
        });
    }

    /** @test */
    public function it_hydrates_auth_when_logged_out()
    {
        $this->get('/');

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $this->assertFalse($cascade['logged_in']);
            $this->assertTrue($cascade['logged_out']);
        });
    }

    /** @test */
    public function it_hydrates_dates()
    {
        Carbon::setTestNow($now = Carbon::create(2018, 2, 3, 19));

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) use ($now) {
            $this->assertEquals($now, $cascade['current_date']);
            $this->assertEquals($now, $cascade['now']);
            $this->assertEquals($now, $cascade['today']);
        });
    }

    /** @test */
    public function it_hydrates_request_variables()
    {
        $this->get('/test');

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('http://test.com/test', $cascade['current_url']);
            $this->assertEquals('/test', $cascade['current_uri']);
        });
    }

    /** @test */
    public function it_hydrates_current_site_variables()
    {
        $cascade = $this->cascade()->withSite(Site::get('en'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('http://test.com', $cascade['homepage']);

            $this->assertEquals('en', $cascade['site']);
            $this->assertEquals('English', $cascade['site_name']);
            $this->assertEquals('en_US', $cascade['site_locale']);
            $this->assertEquals('en', $cascade['site_short_locale']);
            $this->assertEquals('http://test.com', $cascade['site_url']);

            $this->assertEquals('en', $cascade['locale']);
            $this->assertEquals('English', $cascade['locale_name']);
            $this->assertEquals('en_US', $cascade['locale_full']);
            $this->assertEquals('http://test.com', $cascade['locale_url']);
        });
    }

    /** @test */
    public function it_hydrates_current_site_variables_for_subdomain()
    {
        $cascade = $this->cascade()->withSite(Site::get('fr'));

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('http://fr.test.com', $cascade['homepage']);

            $this->assertEquals('fr', $cascade['site']);
            $this->assertEquals('French', $cascade['site_name']);
            $this->assertEquals('fr_FR', $cascade['site_locale']);
            $this->assertEquals('fr', $cascade['site_short_locale']);
            $this->assertEquals('http://fr.test.com', $cascade['site_url']);

            $this->assertEquals('fr', $cascade['locale']);
            $this->assertEquals('French', $cascade['locale_name']);
            $this->assertEquals('fr_FR', $cascade['locale_full']);
            $this->assertEquals('http://fr.test.com', $cascade['locale_url']);
        });
    }

    /** @test */
    public function it_hydrates_current_site_variables_for_subdirectory()
    {
        $cascade = $this->cascade()->withSite(Site::get('de'));

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('http://test.com/de', $cascade['homepage']);

            $this->assertEquals('de', $cascade['site']);
            $this->assertEquals('German', $cascade['site_name']);
            $this->assertEquals('de_DE', $cascade['site_locale']);
            $this->assertEquals('de', $cascade['site_short_locale']);
            $this->assertEquals('http://test.com/de', $cascade['site_url']);

            $this->assertEquals('de', $cascade['locale']);
            $this->assertEquals('German', $cascade['locale_name']);
            $this->assertEquals('de_DE', $cascade['locale_full']);
            $this->assertEquals('http://test.com/de', $cascade['locale_url']);
        });
    }

    /** @test */
    public function it_hydrates_sanitized_post_values()
    {
        $this->post('/', [
            'foo' => 'bar',
            'script' => '<script>',
            'tag' => '{{ foo }}',
        ]);

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $expectedPost = ['foo' => 'bar', 'script' => '&lt;script&gt;', 'tag' => '&lbrace;&lbrace; foo &rbrace;&rbrace;'];
            $this->assertEquals($expectedPost, $cascade['post']);
            $this->assertEquals([], $cascade['get']);
            $this->assertEquals($expectedPost, $cascade['get_post']);
        });
    }

    /** @test */
    public function it_hydrates_sanitized_get_values()
    {
        $this->get('/?foo=bar&script=<script>&tag={{ foo }}');

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $expectedGet = ['foo' => 'bar', 'script' => '&lt;script&gt;', 'tag' => '&lbrace;&lbrace; foo &rbrace;&rbrace;'];
            $this->assertEquals($expectedGet, $cascade['get']);
            $this->assertEquals([], $cascade['post']);
            $this->assertEquals($expectedGet, $cascade['get_post']);
        });
    }

    /** @test */
    public function it_hydrates_sanitized_get_and_post_values()
    {
        $this->post('/?getfoo=bar&getscript=<script>&gettag={{ foo }}', [
            'postfoo' => 'bar',
            'postscript' => '<script>',
            'posttag' => '{{ foo }}',
        ]);

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $expectedGet = ['getfoo' => 'bar', 'getscript' => '&lt;script&gt;', 'gettag' => '&lbrace;&lbrace; foo &rbrace;&rbrace;'];
            $expectedPost = ['postfoo' => 'bar', 'postscript' => '&lt;script&gt;', 'posttag' => '&lbrace;&lbrace; foo &rbrace;&rbrace;'];
            $this->assertEquals($expectedGet, $cascade['get']);
            $this->assertEquals($expectedPost, $cascade['post']);
            $this->assertEquals(array_merge($expectedPost, $expectedGet), $cascade['get_post']);
        });
    }

    /** @test */
    public function it_hydrates_sanitized_old_values()
    {
        session()->put('_old_input', [
            'foo' => 'bar',
            'script' => '<script>',
            'tag' => '{{ foo }}',
        ]);

        $this->get('/');

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) {
            $expected = ['foo' => 'bar', 'script' => '&lt;script&gt;', 'tag' => '&lbrace;&lbrace; foo &rbrace;&rbrace;'];
            $this->assertEquals($expected, $cascade['old']);
        });
    }

    /** @test */
    public function it_hydrates_segments()
    {
        $this->get('/one/two/three/four/five');

        $cascade = $this->cascade()->withSite(Site::get('en'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('one', $cascade['segment_1']);
            $this->assertEquals('two', $cascade['segment_2']);
            $this->assertEquals('three', $cascade['segment_3']);
            $this->assertEquals('four', $cascade['segment_4']);
            $this->assertEquals('five', $cascade['segment_5']);
            $this->assertArrayNotHasKey('segment_6', $cascade);
            $this->assertEquals('five', $cascade['last_segment']);
        });
    }

    /** @test */
    public function it_hydrates_segments_in_subdirectory_site()
    {
        $this->get('/de/one/two/three/four/five');

        $cascade = $this->cascade()->withSite(Site::get('de'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('one', $cascade['segment_1']);
            $this->assertEquals('two', $cascade['segment_2']);
            $this->assertEquals('three', $cascade['segment_3']);
            $this->assertEquals('four', $cascade['segment_4']);
            $this->assertEquals('five', $cascade['segment_5']);
            $this->assertArrayNotHasKey('segment_6', $cascade);
            $this->assertEquals('five', $cascade['last_segment']);
        });
    }

    /** @test */
    public function it_hydrates_segments_on_the_home_page()
    {
        $this->get('/');

        $cascade = $this->cascade()->withSite(Site::get('en'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertArrayNotHasKey('segment_1', $cascade);
            $this->assertArrayNotHasKey('last_segment', $cascade);
        });
    }

    /** @test */
    public function it_hydrates_segments_on_the_home_page_in_subdirectory_site()
    {
        $this->get('de');

        $cascade = $this->cascade()->withSite(Site::get('de'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertArrayNotHasKey('segment_1', $cascade);
            $this->assertArrayNotHasKey('last_segment', $cascade);
        });
    }

    /** @test */
    public function last_segment_doesnt_contain_query_params()
    {
        $this->get('/foo?bar=baz');

        $cascade = $this->cascade()->withSite(Site::get('en'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('foo', $cascade['segment_1']);
            $this->assertEquals('foo', $cascade['last_segment']);
        });
    }

    /** @test */
    public function last_segment_doesnt_contain_query_params_in_subdirectory_site()
    {
        $this->get('/de/foo?bar=baz');

        $cascade = $this->cascade()->withSite(Site::get('de'));

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('foo', $cascade['segment_1']);
            $this->assertEquals('foo', $cascade['last_segment']);
        });
    }

    /** @test */
    public function it_hydrates_page_data()
    {
        $vars = ['foo' => 'bar', 'baz' => 'qux'];
        $page = EntryFactory::id('test')
            ->collection('example')
            ->data($vars)
            ->make();
        $cascade = $this->cascade()->withContent($page);

        $this->assertEquals($page, $cascade->content());

        tap($cascade->hydrate()->toArray(), function ($cascade) use ($vars) {
            $this->assertArrayHasKey('page', $cascade);
            $this->assertArraySubset($vars, $cascade['page']);

            // Everything inside the 'page' array should also be in the top level.
            foreach ($cascade['page'] as $key => $value) {
                $this->assertArrayHasKey($key, $cascade);
            }
        });
    }

    /** @test */
    public function it_hydrates_globals()
    {
        $this->createGlobal('global', $globals = ['foo' => 'bar']);
        $this->createGlobal('scoped_globals', $scopedGlobals = ['baz' => 'qux']);

        tap($this->cascade()->hydrate()->toArray(), function ($cascade) use ($globals, $scopedGlobals) {
            $this->assertArrayHasKey('global', $cascade);
            $this->assertEquals($globals, $cascade['global']);

            $this->assertArrayHasKey('scoped_globals', $cascade);
            $this->assertEquals($scopedGlobals, $cascade['scoped_globals']);

            // Everything inside the 'global' array should also be in the top level.
            foreach ($cascade['global'] as $key => $value) {
                $this->assertArrayHasKey($key, $cascade);
            }
        });
    }

    /** @test */
    public function page_data_overrides_globals()
    {
        $this->withoutEvents(); // prevents taxonomy term tracker from kicking in.

        $page = EntryFactory::id('test')
            ->collection('example')
            ->data(['foo' => 'foo defined in page'])
            ->make();

        $this->createGlobal('global', ['foo' => 'foo defined in global']);

        $cascade = $this->cascade()->withContent($page);

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('foo defined in page', $cascade['foo']);
            $this->assertEquals('foo defined in page', $cascade['page']['foo']);
            $this->assertEquals('foo defined in global', $cascade['global']['foo']);
        });
    }

    /** @test */
    public function it_merges_view_model_data()
    {
        $page = EntryFactory::id('test')
            ->collection('example')
            ->data([
                'foo' => 'foo defined in page',
                'view_model' => 'Tests\View\FakeViewModel',
            ])
            ->make();

        $cascade = $this->cascade()->withContent($page);

        tap($cascade->hydrate()->toArray(), function ($cascade) {
            $this->assertEquals('foo defined in view model', $cascade['foo']);
            $this->assertEquals('foo defined in page', $cascade['page']['foo']);
        });
    }

    private function fakeSiteConfig()
    {
        config(['app.url' => 'http://test.com']);
        url()->forceRootUrl(config('app.url'));
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);
    }

    private function createGlobal($handle, $data)
    {
        $global = GlobalSet::make()->handle($handle);
        $global->addLocalization(
            $global->makeLocalization('en')->data($data)
        );
        $global->save();
    }
}

class FakeSite extends \Statamic\Sites\Site
{
    public function __construct()
    {
        parent::__construct('en', config('statamic.sites.sites.en'));
    }
}

class FakeViewModel extends \Statamic\View\ViewModel
{
    public function data(): array
    {
        return [
            'foo' => 'foo defined in view model',
        ];
    }
}
