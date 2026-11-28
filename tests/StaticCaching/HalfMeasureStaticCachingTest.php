<?php

namespace Tests\StaticCaching;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Facades\Collection;
use Statamic\Facades\Form;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HalfMeasureStaticCachingTest extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Use the file driver so that serializing happens, to better simulate a real world scenario.
        $app['config']->set('cache.default', 'file');

        $app['config']->set('statamic.static_caching.strategy', 'half');

        $app['config']->set('statamic.static_caching.replacers', array_merge($app['config']->get('statamic.static_caching.replacers'), [
            'test' => TestReplacer::class,
        ]));
    }

    #[Test]
    public function it_statically_caches()
    {
        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1> {{ content }}');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'headers' => [
                    'foo' => 'bar',
                    'alfa' => ['bravo', 'charlie'],
                ],
            ],
        ]);

        $response = $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> <p>This is the about page.</p>', false);
        $this->assertEquals(['bar'], $response->headers->all('foo'));
        $this->assertEquals(['bravo', 'charlie'], $response->headers->all('alfa'));

        $page
            ->set('content', 'Updated content')
            ->set('headers', ['foo' => 'updated', 'alfa' => ['updated1', 'updated2']])
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $response = $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> <p>This is the about page.</p>', false);
        $this->assertEquals(['bar'], $response->headers->all('foo'));
        $this->assertEquals(['bravo', 'charlie'], $response->headers->all('alfa'));
    }

    #[Test]
    public function it_serves_head_requests_from_the_cache()
    {
        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ example_count }}');

        $this->createPage('about');

        $this->get('/about')->assertOk()->assertSee('1');

        $this->head('/about')->assertNoContent(200);

        $this->assertEquals(1, app('example_count'));
    }

    #[Test]
    public function it_doesnt_statically_cache_head_requests()
    {
        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1>');

        $page = $this->createPage('about', ['with' => ['title' => 'The About Page']]);

        $this->head('/about')->assertNoContent(200);

        $page
            ->set('title', 'Updated title')
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>Updated title</h1>', false);
    }

    #[Test]
    public function it_performs_replacements()
    {
        Carbon::setTestNow(Carbon::parse('2019-01-01'));

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ now format="Y-m-d" }} REPLACEME');

        $this->createPage('about');

        $response = $this->get('/about')->assertOk();
        $this->assertSame('2019-01-01 INITIAL-2019-01-01', $response->getContent());

        Carbon::setTestNow(Carbon::parse('2020-05-23'));
        $response = $this->get('/about')->assertOk();
        $this->assertSame('2019-01-01 SUBSEQUENT-2020-05-23', $response->getContent());
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nocache_tags()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ example_count }} {{ nocache }}{{ example_count }}{{ /nocache }}');

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 2', false);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 3', false);
    }

    #[Test]
    public function it_keeps_view_shared_data_dynamic_within_nocache_tags()
    {
        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ visitor_count }} {{ nocache }}{{ visitor_count }}{{ /nocache }}');

        $this->createPage('about');

        view()->share('visitor_count', 1);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 1', false);

        view()->share('visitor_count', 2);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 2', false);
    }

    public function shareErrorsEnabled($app)
    {
        $app['config']->set('statamic.static_caching.share_errors', true);
    }

    #[Test]
    #[DefineEnvironment('shareErrorsEnabled')]
    public function nocache_session_is_written_under_the_real_url_when_share_errors_is_enabled()
    {
        \Illuminate\Support\Facades\Cache::flush();

        // Regression: when share_errors is enabled, the middleware's copyError()
        // step used to call Request::fakeStaticCacheStatus() on every cacheable
        // response, including 200s. That mutated the singleton nocache Session
        // URL to /__shared-errors/sitename/200, causing Session::write() to persist the
        // regions list under the wrong cache key. On subsequent hits in a
        // fresh PHP process, the cached page was found but its nocache regions
        // could not be restored, a RegionNotFound was caught, and the request
        // fell through to a full dynamic re-render — defeating half-measure
        // caching. See discussion: nocache regions silently failing under
        // share_errors on 200 responses.

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ title }} {{ nocache }}{{ title }}{{ /nocache }}');

        $this->createPage('about', ['with' => ['title' => 'Hello']]);

        $this->get('/about')->assertOk();

        // The session metadata must be persisted under the real request URL,
        // not under the fake /__shared-errors/default/200 URL.
        $store = \Statamic\Facades\StaticCache::cacheStore();
        $this->assertNotNull(
            $store->get('nocache::session.'.md5('http://localhost/about')),
            'Expected nocache session to be stored under the real request URL.'
        );
        $this->assertNull(
            $store->get('nocache::session.'.md5('/__shared-errors/default/200')),
            'nocache session must not be stored under the shared-errors URL for 200 responses.'
        );
    }

    #[Test]
    #[DefineEnvironment('shareErrorsEnabled')]
    public function shared_errors_are_only_cached_under_the_shared_errors_url()
    {
        \Illuminate\Support\Facades\Cache::flush();

        // With share_errors enabled every error is served from the one shared copy,
        // so it isn't also cached under its own URL. Caching it there too would add
        // every distinct erroring URL to the cached URL index, which grows without
        // bound when bots or broken relative links request endless unique URLs. The
        // index is a single value rewritten under a lock on every cache write, so
        // that growth slows down every request that caches a page.

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('errors.layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', '404 {{ nocache }}dynamic{{ /nocache }}');

        $this->get('/this-does-not-exist')->assertNotFound()->assertSee('404 dynamic', false);
        $this->get('/neither-does-this')->assertNotFound()->assertSee('404 dynamic', false);
        $this->get('/this-does-not-exist')->assertNotFound()->assertSee('404 dynamic', false);

        $this->assertEquals(['/__shared-errors/en/404'], app(Cacher::class)->getUrls()->values()->all());

        $store = \Statamic\Facades\StaticCache::cacheStore();

        // The shared copy's nocache regions are restored by the shared-errors URL...
        $this->assertNotNull(
            $store->get('nocache::session.'.md5('/__shared-errors/en/404')),
            'Expected nocache session to be stored under the shared-errors URL.'
        );

        // ...and nothing is cached under the real URL, so there's no session there to restore.
        $this->assertNull(
            $store->get('nocache::session.'.md5('http://localhost/this-does-not-exist')),
            'nocache session must not be stored under the real URL for shared errors.'
        );
    }

    #[Test]
    public function it_caches_and_tracks_404s()
    {
        \Illuminate\Support\Facades\Cache::flush();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('errors.404', '404 not found');

        $this->get('/this-does-not-exist')->assertNotFound();

        $this->assertEquals(['/this-does-not-exist'], app(Cacher::class)->getUrls()->values()->all());

        $response = $this->get('/this-does-not-exist')->assertNotFound();
        $this->assertTrue($response->wasStaticallyCached());
    }

    #[Test]
    public function invalidating_a_cached_404_lets_new_content_be_served()
    {
        \Illuminate\Support\Facades\Cache::flush();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ title }}');
        $this->viewShouldReturnRaw('errors.404', '404 not found');

        // The URL 404s before the page exists, and the 404 gets cached.
        $this->get('/about')->assertNotFound();

        // Publishing a page at that URL invalidates the cached 404...
        $this->createPage('about', ['with' => ['title' => 'The About Page']]);
        app(Cacher::class)->invalidateUrls(['/about']);

        // ...so the new page is served instead of the stale 404.
        $this->get('/about')->assertOk()->assertSee('The About Page');
    }

    #[Test]
    public function moving_an_entry_in_the_tree_invalidates_the_cached_404_at_its_new_url()
    {
        \Illuminate\Support\Facades\Cache::flush();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ title }}');
        $this->viewShouldReturnRaw('errors.404', '404 not found');

        $collection = tap(Collection::make('pages')->routes('{parent_uri}/{slug}')->template('default'))->save();
        $this->createPage('parent', ['with' => ['title' => 'The Parent Page']]);
        $this->createPage('child', ['with' => ['title' => 'The Child Page']]);
        $collection->structureContents(['root' => false])->save();
        $collection->structure()->makeTree('en', [['entry' => 'parent'], ['entry' => 'child']])->save();

        // Both pages are top level, so this URL 404s and the 404 gets cached.
        $this->get('/parent/child')->assertNotFound();

        // Moving the child page below the parent page makes the URL valid...
        $collection->structure()->in('en')->tree([['entry' => 'parent', 'children' => [['entry' => 'child']]]])->save();

        // ...so the page is served instead of the stale 404.
        $this->get('/parent/child')->assertOk()->assertSee('The Child Page');
    }

    #[Test]
    public function wildcard_refresh_invalidates_cached_404s_instead_of_warming_them()
    {
        \Illuminate\Support\Facades\Cache::flush();

        Queue::fake();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ title }}');
        $this->viewShouldReturnRaw('errors.404', '404 not found');

        $this->createPage('about', ['with' => ['title' => 'The About Page']]);

        // A real page, matching the wildcard `/about*` rule below.
        $this->get('/about')->assertOk();

        // A junk URL that also matches the wildcard prefix, but doesn't resolve
        // to real content (e.g. a bot/scanner probe under the same path).
        $this->get('/about-this-does-not-exist')->assertNotFound();

        app(Cacher::class)->refreshUrls(['/about*']);

        Queue::assertPushed(StaticWarmJob::class, function ($job) {
            return str_contains((string) $job->request->getUri(), '/about')
                && ! str_contains((string) $job->request->getUri(), 'this-does-not-exist');
        });
        Queue::assertNotPushed(StaticWarmJob::class, function ($job) {
            return str_contains((string) $job->request->getUri(), 'this-does-not-exist');
        });

        // The junk URL is invalidated rather than warmed, so the tracked
        // set converges to real pages.
        $this->assertEquals(['/about'], app(Cacher::class)->getUrls()->values()->all());
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nocache_tags_in_loops()
    {
        // Use a tag that outputs something dynamic but consistent.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function wildcard($method)
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $this->context->get($method).$count;
            }
        })::register();

        $this->withStandardFakeViews();

        $template = <<<'EOT'
    {{ array }}
        {{ value }}
        {{ example_count:value }}
        {{ nocache }}
            {{ value }}
            {{ example_count:value }}
        {{ /nocache }}
    {{ /array }}
    EOT;

        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('about', ['with' => [
            'array' => [
                ['value' => 'One'],
                ['value' => 'Two'],
                ['value' => 'Three'],
            ],
        ]]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([
                'One', 'One1', 'One', 'One4',
                'Two', 'Two2', 'Two', 'Two5',
                'Three', 'Three3', 'Three', 'Three6',
            ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([
                'One', 'One1', 'One', 'One7',
                'Two', 'Two2', 'Two', 'Two8',
                'Three', 'Three3', 'Three', 'Three9',
            ]);
    }

    #[Test]
    public function it_can_keep_the_cascade_parts_dynamic_using_nocache_tags()
    {
        // The "now" variable is generated in the cascade on every request.

        Carbon::setTestNow(Carbon::parse('2019-01-01'));

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ now format="Y-m-d" }} {{ nocache }}{{ now format="Y-m-d" }}{{ /nocache }}');

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('2019-01-01 2019-01-01', false);

        Carbon::setTestNow(Carbon::parse('2020-05-23'));

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('2019-01-01 2020-05-23', false);
    }

    #[Test]
    public function it_can_keep_the_urls_page_parts_dynamic_using_nocache_tags()
    {
        // The "page" variable (i.e. the about entry) is inserted into the cascade on every request.

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1> {{ text }} {{ nocache }}{{ text }}{{ /nocache }}');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'text' => 'This is the about page.',
            ],
        ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> This is the about page. This is the about page.', false);

        $page
            ->set('text', 'Updated text')
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> This is the about page. Updated text', false);
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nested_nocache_tags()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $template = <<<'EOT'
{{ example_count }}
{{ nocache }}
    {{ example_count }}
    {{ nocache }}
        {{ example_count }}
    {{ /nocache }}
{{ /nocache }}
EOT;

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([1, 2, 3]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([1, 4, 5]);
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nocache_tags_with_view_front_matter()
    {
        $template = <<<'EOT'
---
foo: bar
---
{{ view:foo }} {{ nocache }}{{ view:foo }}{{ /nocache }}
EOT;

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('bar bar');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('bar bar');
    }

    public function bladeViewPaths($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/blade',
            ...$app['config']->get('view.paths'),
        ]);
    }

    #[Test]
    #[DefineEnvironment('bladeViewPaths')]
    public function it_can_keep_parts_dynamic_using_blade()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        app()->instance('example_count_tag', function () {
            $count = app('example_count');
            $count++;
            app()->instance('example_count', $count);

            return $count;
        });

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 2', false);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 3', false);
    }

    #[Test]
    public function it_does_not_cache_a_page_with_a_form_prefilled_from_a_partial_submission()
    {
        $form = $this->makeMultiPageForm();
        $submission = tap($form->makeSubmission()->data(['name' => 'Olaf'])->asPartial())->save();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ form:survey }}{{ fields }}{{ handle }}=[{{ value }}]{{ /fields }}{{ /form:survey }}');
        $this->createPage('about');

        $this
            ->withSession(['form.survey.partial_submission' => $submission->id()])
            ->get('/about?page=page_one')
            ->assertOk()
            ->assertSee('name=[Olaf]', false);

        $this->flushSession();

        $this
            ->get('/about?page=page_one')
            ->assertOk()
            ->assertSee('name=[]', false)
            ->assertDontSee('Olaf');
    }

    #[Test]
    public function it_caches_a_page_with_a_form_when_there_is_no_partial_submission()
    {
        $this->makeMultiPageForm();

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                app()->instance('example_count', $count = app('example_count') + 1);

                return $count;
            }
        })::register();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ example_count }} {{ form:survey }}{{ fields }}{{ handle }}{{ /fields }}{{ /form:survey }}');
        $this->createPage('about');

        $this->get('/about')->assertOk()->assertSee('1 <form', false);
        $this->get('/about')->assertOk()->assertSee('1 <form', false);
    }

    #[Test]
    public function it_still_caches_a_page_when_the_prefilled_form_is_inside_nocache_tags()
    {
        $form = $this->makeMultiPageForm();
        $submission = tap($form->makeSubmission()->data(['name' => 'Olaf'])->asPartial())->save();

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                app()->instance('example_count', $count = app('example_count') + 1);

                return $count;
            }
        })::register();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ example_count }} {{ nocache }}{{ form:survey }}{{ fields }}{{ handle }}=[{{ value }}]{{ /fields }}{{ /form:survey }}{{ /nocache }}');
        $this->createPage('about');

        $this
            ->withSession(['form.survey.partial_submission' => $submission->id()])
            ->get('/about')
            ->assertOk()
            ->assertSee('1 <form', false)
            ->assertSee('name=[Olaf]', false);

        $this->flushSession();

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 <form', false)
            ->assertSee('name=[]', false);
    }

    private function makeMultiPageForm()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = Form::make('survey')->formFields([
            'pages' => [
                ['id' => 'page_one', 'sections' => [['fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]]]],
                ['id' => 'page_two', 'sections' => [['fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]]]],
            ],
        ]);

        Form::shouldReceive('find')->with('survey')->andReturn($form);
        Form::makePartial();

        return tap($form)->save();
    }
}

class TestReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial)
    {
        $initial->setContent(
            str_replace('REPLACEME', 'INITIAL-'.Carbon::now()->format('Y-m-d'), $initial->getContent())
        );
    }

    public function replaceInCachedResponse(Response $response)
    {
        $response->setContent(
            str_replace('REPLACEME', 'SUBSEQUENT-'.Carbon::now()->format('Y-m-d'), $response->getContent())
        );
    }
}
