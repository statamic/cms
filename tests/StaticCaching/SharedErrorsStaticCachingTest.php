<?php

namespace Tests\StaticCaching;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SharedErrorsStaticCachingTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    private ApplicationCacher $cacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacher = new ApplicationCacher($this->app['cache']->store('array'), []);
    }

    #[Test]
    public function the_shared_error_is_scoped_to_the_current_site()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        // Render and share the english 404 first.
        Site::setCurrent('english');
        $this->shareError(404, 'English not found');

        // The french site must not be considered to already have a shared error.
        // Before scoping by site, the english error would leak here and break the
        // localization (e.g. the language picker) for every other site.
        Site::setCurrent('french');
        $this->assertFalse($this->cacher->hasCachedPage($this->sharedErrorRequest(404)));

        $this->shareError(404, 'French not found');

        // Each site now serves its own localized shared error.
        Site::setCurrent('english');
        $this->assertTrue($this->cacher->hasCachedPage($this->sharedErrorRequest(404)));
        $this->assertEquals('English not found', $this->cacher->getCachedPage($this->sharedErrorRequest(404))->content);

        Site::setCurrent('french');
        $this->assertTrue($this->cacher->hasCachedPage($this->sharedErrorRequest(404)));
        $this->assertEquals('French not found', $this->cacher->getCachedPage($this->sharedErrorRequest(404))->content);
    }

    /**
     * Replicates the request that's built when a shared error is written
     * (the Cache middleware's copyError) and read (RendersHttpExceptions's
     * getCachedError) for the current site.
     */
    private function sharedErrorRequest(int $status): Request
    {
        return Request::createFrom(Request::create('http://localhost/'))->fakeStaticCacheStatus($status);
    }

    /**
     * Cache a shared error for the current site, mirroring how the cacher
     * persists pages once the response has been prepared.
     */
    private function shareError(int $status, string $content): void
    {
        $request = $this->sharedErrorRequest($status);
        $response = response($content, $status);

        $this->cacher->cachePage($request, $response);

        // The cacher writes to the store on the ResponsePrepared event, which
        // the framework fires during the real response lifecycle.
        event(new ResponsePrepared($request, $response));
    }

    public function withTestReplacer($app)
    {
        $app['config']->set('statamic.static_caching.strategy', 'half');
        $app['config']->set('statamic.static_caching.share_errors', true);
        $app['config']->set('statamic.static_caching.replacers', array_merge(
            $app['config']->get('statamic.static_caching.replacers'),
            ['test' => SharedErrorTestReplacer::class]
        ));
    }

    #[Test]
    #[DefineEnvironment('withTestReplacer')]
    public function replacers_run_when_serving_a_shared_error()
    {
        Cache::flush();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('errors.layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', '404 LIVE_VALUE');

        // First 404 renders live and seeds the shared cache. The live response
        // itself is untouched by the prepare step - only the clone that gets
        // cached is.
        $this->get('/this-does-not-exist')
            ->assertNotFound()
            ->assertSee('404 LIVE_VALUE', false);

        // A different URL that also 404s is served from the shared cache.
        // Regression: previously this replayed whatever copyError() captured
        // before any replacer ran, and getCachedError() never ran replacers on
        // the way out either - so a stale value (or, for CsrfTokenReplacer in
        // production, a frozen CSRF token from a different session) leaked to
        // every subsequent visitor forever.
        $this->get('/this-also-does-not-exist')
            ->assertNotFound()
            ->assertSee('404 REPLACED_ON_SERVE', false);
    }

    public function shareErrorsWithHalfMeasure($app)
    {
        $app['config']->set('statamic.static_caching.strategy', 'half');
        $app['config']->set('statamic.static_caching.share_errors', true);
    }

    #[Test]
    #[DefineEnvironment('shareErrorsWithHalfMeasure')]
    public function each_session_gets_its_own_csrf_token_on_a_shared_error()
    {
        Cache::flush();

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('errors.layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', '404 {{ csrf_token }}');

        // First 404 renders live and seeds the shared cache.
        $this->withSession(['_token' => 'session-one-token'])
            ->get('/nope-one')
            ->assertNotFound()
            ->assertSee('404 session-one-token', false);

        // A different URL that also 404s is served from the shared cache, but
        // for a different session. It must get its own token, not the one
        // frozen into the shared cache by the first visitor.
        $this->withSession(['_token' => 'session-two-token'])
            ->get('/nope-two')
            ->assertNotFound()
            ->assertSee('404 session-two-token', false)
            ->assertDontSee('session-one-token', false);

        // A repeat hit on that second URL, now cached under its own URL,
        // must still resolve session two's current token, not a frozen one.
        $this->withSession(['_token' => 'session-two-token'])
            ->get('/nope-two')
            ->assertNotFound()
            ->assertSee('404 session-two-token', false);
    }

    #[Test]
    #[DefineEnvironment('shareErrorsWithHalfMeasure')]
    public function nocache_region_inside_a_shared_error_stays_dynamic_across_repeat_hits()
    {
        Cache::flush();

        // Use a tag that outputs something dynamic. It just increments by
        // one every time it's rendered.
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
        $this->viewShouldReturnRaw('errors.layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', '404 {{ nocache }}{{ example_count }}{{ /nocache }}');

        // First 404 renders live and seeds the shared cache.
        $this->get('/nope-one')->assertNotFound()->assertSee('404 1', false);

        // A different URL that also 404s is served from the shared cache.
        // Its nocache region must render fresh, not replay whatever the
        // shared cache captured when it was seeded.
        $this->get('/nope-two')->assertNotFound()->assertSee('404 2', false);

        // Repeat hits to that second URL, now cached under its own URL, must
        // keep rendering the region fresh too. Before the fix, the first
        // request served from the shared cache baked the region's rendered
        // output into the per-URL cache entry, so every later hit on that
        // URL replayed the same frozen value forever - the same class of
        // cross-visitor leak this PR fixes for CSRF tokens.
        $this->get('/nope-two')->assertNotFound()->assertSee('404 3', false);
        $this->get('/nope-two')->assertNotFound()->assertSee('404 4', false);
    }
}

class SharedErrorTestReplacer implements Replacer
{
    const PLACEHOLDER = 'TEST_TOKEN_PLACEHOLDER';

    public function prepareResponseToCache(Response $response, Response $initial)
    {
        // Mirrors CsrfTokenReplacer's behaviour for the "half" strategy:
        // only the clone that gets cached is touched, not $initial (which
        // is what's returned to this first request).
        $response->setContent(str_replace('LIVE_VALUE', self::PLACEHOLDER, $response->getContent()));
    }

    public function replaceInCachedResponse(Response $response)
    {
        $response->setContent(str_replace(self::PLACEHOLDER, 'REPLACED_ON_SERVE', $response->getContent()));
    }
}
