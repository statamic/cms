<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StaticWarmJob;
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
    public function nocache_session_is_written_under_the_real_url_for_shared_errors()
    {
        \Illuminate\Support\Facades\Cache::flush();

        // Regression: when share_errors is enabled, rendering an error repoints the
        // singleton nocache Session URL at /__shared-errors/<site>/<status> (via
        // RendersHttpExceptions::getCachedError and the middleware's copyError).
        // Session::write() then persisted the regions list only under that shared
        // URL. But half-measure caching also stores the error page under its real
        // URL, so a repeat request to that same URL restored the session by its
        // real URL, found nothing, caught a RegionNotFound, and fell through to a
        // full dynamic re-render. The session must be persisted under both URLs.

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('errors.layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', '404 {{ nocache }}dynamic{{ /nocache }}');

        $this->get('/this-does-not-exist')->assertNotFound();

        $store = \Statamic\Facades\StaticCache::cacheStore();

        // Stored under the real request URL so repeat requests (served from the
        // per-URL cache) can restore their nocache regions.
        $this->assertNotNull(
            $store->get('nocache::session.'.md5('http://localhost/this-does-not-exist')),
            'Expected nocache session to be stored under the real request URL.'
        );

        // Still stored under the shared-errors URL so the same error served for
        // other erroring URLs can restore its nocache regions.
        $this->assertNotNull(
            $store->get('nocache::session.'.md5('/__shared-errors/en/404')),
            'Expected nocache session to be stored under the shared-errors URL.'
        );
    }

    #[Test]
    public function it_does_not_track_404_urls()
    {
        \Illuminate\Support\Facades\Cache::flush();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('errors.404', '404 not found');

        $this->get('/this-does-not-exist')->assertNotFound();

        $cacher = app(Cacher::class);
        $this->assertEquals([], $cacher->getUrls()->all());

        // The 404 response is still served from the cache on a repeat hit,
        // even though it was never added to the tracked `.urls` set.
        $response = $this->get('/this-does-not-exist')->assertNotFound();
        $this->assertTrue($response->wasStaticallyCached());
    }

    #[Test]
    public function wildcard_invalidation_does_not_warm_untracked_404_urls()
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
