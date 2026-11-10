<?php

namespace Tests\Feature\Utilities;

use Facades\Tests\Factories\EntryFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StaticWarm;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Events\StacheCleared;
use Statamic\Events\StacheWarmed;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\StaticCache;
use Statamic\Facades\User;
use Statamic\Http\Controllers\FrontendController;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function cachePages(array $urls, array $strategy = ['driver' => 'application'])
    {
        $this->setSites([
            'en' => ['name' => 'English', 'url' => 'https://example.com', 'locale' => 'en_US'],
            'fr' => ['name' => 'French', 'url' => 'https://example.com/fr', 'locale' => 'fr_FR'],
            'de' => ['name' => 'German', 'url' => 'https://example.de', 'locale' => 'de_DE'],
        ]);

        config([
            'statamic.static_caching.strategy' => 'explorer',
            'statamic.static_caching.strategies.explorer' => $strategy,
        ]);

        $cacher = StaticCache::driver();

        foreach ($urls as $url) {
            $parts = parse_url($url);
            $path = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
            $cacher->cacheUrl(md5($url), $path, $parts['scheme'].'://'.$parts['host']);
        }

        $this->actingAs(tap(User::make()->makeSuper())->save());

        return $cacher;
    }

    /**
     * @see https://github.com/statamic/cms/issues/15623
     */
    #[Test]
    public function it_defers_the_image_cache_stats()
    {
        config(['statamic.assets.image_manipulation.cache' => 'glide']);

        Storage::fake('glide');
        Storage::disk('glide')->put('containers/main/foo.jpg/abc/foo.jpg', 'foo');
        Storage::disk('glide')->put('containers/main/bar.jpg/def/bar.jpg', 'barbaz');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('utilities.cache'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('utilities/Cache')
                ->missing('images')
                ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                    ->where('images.count', 2)
                    ->where('images.assets', 2)
                    ->where('images.size', '9 B')
                )
            );
    }

    #[Test]
    public function it_counts_distinct_cached_assets_without_counting_paths_or_remote_images_as_assets()
    {
        config(['statamic.assets.image_manipulation.cache' => 'glide']);
        Storage::fake('glide');

        foreach ([
            'containers/main/nested/photo.jpg/one/photo.jpg',
            'containers/main/nested/photo.jpg/two/photo.webp',
            'containers/other/nested/photo.jpg/one/photo.jpg',
            'paths/photo.jpg/one/photo.jpg',
            'http/example.com/photo.jpg/one/photo.jpg',
        ] as $path) {
            Storage::disk('glide')->put($path, '123');
        }

        $this->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('utilities.cache'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                    ->where('images.count', 5)
                    ->where('images.assets', 2)
                    ->where('images.size', '15 B')
                )
            );
    }

    #[Test]
    public function it_reports_coverage_and_lists_only_uncached_warmable_urls()
    {
        $cacher = $this->cachePages(['https://example.com/expired']);
        config(['statamic.static_caching.exclude' => ['/excluded']]);

        Collection::make('pages')->routes('/{slug}')->sites(['en', 'fr', 'de'])->save();

        foreach (['about', 'missing', 'expired', 'excluded'] as $slug) {
            EntryFactory::collection('pages')->slug($slug)->create();
        }

        EntryFactory::collection('pages')->slug('draft')->published(false)->create();
        EntryFactory::collection('pages')->slug('redirect')->data(['redirect' => '/about'])->create();
        EntryFactory::collection('pages')->slug('bonjour')->locale('fr')->create();
        EntryFactory::collection('pages')->slug('hallo')->locale('de')->create();

        Route::get('/custom', FrontendController::class.'@route');
        Route::get('/dynamic/{slug}', FrontendController::class.'@route');
        StaticWarm::hook('additional', fn () => collect(['/extra', '/extra']));

        $request = Request::create('https://example.com/about');
        $response = response('Cached');
        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));
        Site::setCurrent('en');

        $this->get(cp_route('utilities.cache'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->missing('staticCoverage')
                ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                    ->where('staticCoverage', ['cached' => 1, 'total' => 7, 'uncached' => 6])
                )
            );
        $this->assertSame('en', Site::current()->handle());

        $result = $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached', 'perPage' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 6)
            ->assertJsonPath('meta.last_page', 3);
        $columns = collect($result->json('meta.columns'))->pluck('field');
        $this->assertNotContains('generated_at', $columns);
        $this->assertContains('actions', $columns);

        $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached', 'site' => 'fr', 'search' => 'bon']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.url', 'https://example.com/fr/bonjour');

        $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached', 'domain' => 'https://example.de']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.url', 'https://example.de/hallo');
    }

    #[Test]
    public function it_reports_zero_coverage_when_there_are_no_warmable_urls_and_skips_it_when_disabled()
    {
        $this->cachePages([]);

        $this->get(cp_route('utilities.cache'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                    ->where('staticCoverage', ['cached' => 0, 'total' => 0, 'uncached' => 0])
                )
            );

        config(['statamic.static_caching.strategy' => null]);

        $this->get(cp_route('utilities.cache'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                    ->where('staticCoverage', null)
                )
            );
    }

    #[Test]
    public function it_caches_one_warmable_url_and_removes_it_from_the_uncached_list()
    {
        $cacher = $this->cachePages([]);
        Collection::make('pages')->routes('/{slug}')->save();
        EntryFactory::collection('pages')->slug('about')->create();
        EntryFactory::collection('pages')->slug('contact')->create();
        $url = 'https://example.com/about';

        $this->mock(Client::class)->shouldReceive('get')->once()
            ->with($url, ['verify' => true, 'allow_redirects' => false, 'timeout' => 30])
            ->andReturnUsing(function () use ($cacher, $url) {
                $request = Request::create($url);
                $response = response('Cached page');
                $cacher->cachePage($request, $response);
                event(new ResponsePrepared($request, $response));

                return new GuzzleResponse(200);
            });

        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => $url])
            ->assertOk()
            ->assertJsonPath('url', $url)
            ->assertJsonPath('message', fn ($message) => str_starts_with($message, 'Page cached in '));

        $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.url', 'https://example.com/contact');

        $this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.url', $url);
    }

    #[Test]
    public function it_only_accepts_warmable_urls_for_individual_caching()
    {
        $this->cachePages([]);
        config(['statamic.static_caching.exclude' => ['/excluded']]);
        Collection::make('pages')->routes('/{slug}')->save();
        EntryFactory::collection('pages')->slug('draft')->published(false)->create();
        EntryFactory::collection('pages')->slug('excluded')->create();
        $this->mock(Client::class)->shouldNotReceive('get');

        foreach ([
            'https://external.example/about',
            'https://example.com/missing',
            'https://example.com/draft',
            'https://example.com/excluded',
            'https://example.com/*',
        ] as $url) {
            $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => $url])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('url');
        }
    }

    #[Test]
    public function it_does_not_report_a_page_as_cached_when_the_request_fails_or_does_not_cache_it()
    {
        $this->cachePages([]);
        Collection::make('pages')->routes('/{slug}')->save();
        EntryFactory::collection('pages')->slug('about')->create();
        $url = 'https://example.com/about';
        $client = $this->mock(Client::class);
        $client->shouldReceive('get')->once()->andThrow(new ConnectException('Connection failed.', new GuzzleRequest('GET', $url)));
        $client->shouldReceive('get')->once()->andReturn(new GuzzleResponse(200));

        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => $url])
            ->assertStatus(502)
            ->assertJsonPath('message', 'Unable to cache this page.');

        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => $url])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The page could not be cached.');
    }

    #[Test]
    public function it_recognizes_an_already_cached_page_in_its_own_site_directory()
    {
        $disk = Storage::fake('cache-single-page');
        $cacher = $this->cachePages([], [
            'driver' => 'file',
            'path' => [
                'en' => $disk->path('en'),
                'fr' => $disk->path('fr'),
                'de' => $disk->path('de'),
            ],
        ]);
        Collection::make('pages')->routes('/{slug}')->sites(['en', 'fr'])->save();
        EntryFactory::collection('pages')->locale('fr')->slug('bonjour')->create();
        $url = 'https://example.com/fr/bonjour';
        $path = $cacher->getFilePath($url, 'fr');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'Already cached');
        $this->mock(Client::class)->shouldNotReceive('get');

        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => $url])
            ->assertOk()
            ->assertJsonPath('message', 'Page is already cached.');

        $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached']))
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    #[Test]
    public function it_lists_and_caches_a_homepage_without_a_trailing_slash_using_the_file_driver()
    {
        $disk = Storage::fake('cache-homepage');
        $cacher = $this->cachePages([], ['driver' => 'file', 'path' => $disk->path('pages')]);
        $url = 'https://example.com';
        StaticWarm::hook('additional', fn () => collect([$url]));

        $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.url', $url);

        $this->mock(Client::class)->shouldReceive('get')->once()->with($url, Mockery::type('array'))
            ->andReturnUsing(function () use ($cacher, $url) {
                $cacher->cachePage(Request::create($url), 'Cached homepage');

                return new GuzzleResponse(200);
            });

        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => $url])
            ->assertOk()
            ->assertJsonPath('url', $url);

        $this->getJson(cp_route('utilities.cache.static-pages', ['status' => 'uncached']))
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    #[Test]
    public function it_does_not_cache_individual_pages_when_static_caching_is_disabled()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());
        config(['statamic.static_caching.strategy' => null]);
        $this->mock(Client::class)->shouldNotReceive('get');

        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => 'https://example.com/about'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Static caching is not enabled.');
    }

    #[Test]
    public function it_sends_operation_feedback_as_a_toast()
    {
        config(['statamic.static_caching.strategy' => null]);

        $url = cp_route('utilities.cache');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->from($url)
            ->post(cp_route('utilities.cache.clear-static'))
            ->assertRedirect($url)
            ->assertSessionHas('success');

        $message = session('success');

        $this->assertMatchesRegularExpression('/^Static page cache cleared in [\\d.]+(?:ms|s)\\.$/', $message);

        $this->get($url)->assertInertia(fn (AssertableInertia $page) => $page
            ->where('_toasts.0.message', $message)
            ->missing('operationResult')
            ->missing('images')
        );
    }

    #[Test]
    public function it_clears_the_stache_without_warming_it()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());
        Event::fake([StacheCleared::class, StacheWarmed::class]);
        $url = cp_route('utilities.cache');

        $this->from($url)->post(cp_route('utilities.cache.clear-stache'))
            ->assertRedirect($url)
            ->assertSessionHas('success', fn ($message) => str_starts_with($message, 'Stache cleared in '));

        Event::assertDispatched(StacheCleared::class);
        Event::assertNotDispatched(StacheWarmed::class);
    }

    #[Test]
    public function it_warms_the_stache_without_clearing_it()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());
        Event::fake([StacheCleared::class, StacheWarmed::class]);
        $url = cp_route('utilities.cache');

        $this->from($url)->post(cp_route('utilities.cache.warm-stache'))
            ->assertRedirect($url)
            ->assertSessionHas('success', fn ($message) => str_starts_with($message, 'Stache warmed in '));

        Event::assertDispatched(StacheWarmed::class);
        Event::assertNotDispatched(StacheCleared::class);
    }

    #[Test]
    public function it_lists_and_paginates_cached_urls_across_domains()
    {
        $this->cachePages([
            'https://example.com/blog/one',
            'https://example.com/fr/blog/two',
            'https://example.de/blog/three',
        ]);

        $this->getJson(cp_route('utilities.cache.static-pages', ['perPage' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.domains', ['https://example.com', 'https://example.de'])
            ->assertJsonPath('data.1.site', 'fr');

        $this->getJson(cp_route('utilities.cache.static-pages', ['perPage' => 2, 'page' => 2]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.url', 'https://example.de/blog/three')
            ->assertJsonPath('meta.from', 3)
            ->assertJsonPath('meta.to', 3);
    }

    #[Test]
    public function it_searches_cached_urls_and_combines_site_and_domain_filters()
    {
        $this->cachePages([
            'https://example.com/about',
            'https://example.com/blog/one',
            'https://example.com/fr/blog/two',
            'https://example.de/blog/three',
        ]);

        $this->getJson(cp_route('utilities.cache.static-pages', [
            'search' => 'BLOG',
            'site' => 'fr',
            'domain' => 'https://example.com',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.url', 'https://example.com/fr/blog/two');

        $this->getJson(cp_route('utilities.cache.static-pages', ['domain' => 'https://example.de']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.site', 'de');
    }

    #[Test]
    public function it_sorts_by_generation_date_before_pagination_with_unknown_dates_last()
    {
        $timestamps = [
            'https://example.com/blog/a' => now()->subHour()->getTimestamp(),
            'https://example.com/blog/b' => now()->subHours(2)->getTimestamp(),
            'https://example.com/blog/z' => now()->getTimestamp(),
            'https://example.de/unknown' => null,
        ];

        $this->cachePages(array_keys($timestamps));

        foreach ($timestamps as $url => $timestamp) {
            StaticCache::cacheStore()->forever('static-cache:responses:'.md5($url), [
                'content' => 'Cached content',
                'headers' => [],
                'status' => 200,
                'generated_at' => $timestamp,
            ]);
        }

        $this->getJson(cp_route('utilities.cache.static-pages', ['perPage' => 2]))
            ->assertOk()
            ->assertJsonPath('data.0.url', 'https://example.com/blog/z')
            ->assertJsonPath('data.1.url', 'https://example.com/blog/a');

        $this->getJson(cp_route('utilities.cache.static-pages', ['perPage' => 2, 'page' => 2]))
            ->assertOk()
            ->assertJsonPath('data.0.url', 'https://example.com/blog/b')
            ->assertJsonPath('data.1.url', 'https://example.de/unknown');

        $this->getJson(cp_route('utilities.cache.static-pages', ['sort' => 'generated_at', 'order' => 'asc']))
            ->assertOk()
            ->assertJsonPath('data.0.url', 'https://example.com/blog/b')
            ->assertJsonPath('data.1.url', 'https://example.com/blog/a')
            ->assertJsonPath('data.2.url', 'https://example.com/blog/z')
            ->assertJsonPath('data.3.url', 'https://example.de/unknown');
    }

    #[Test]
    public function it_sorts_paths_naturally_and_preserves_query_strings()
    {
        $this->cachePages([
            'https://example.com/blog/10',
            'https://example.com/blog/2?view=full',
            'https://example.de/about',
        ]);

        $this->getJson(cp_route('utilities.cache.static-pages', ['sort' => 'path', 'order' => 'asc']))
            ->assertOk()
            ->assertJsonPath('data.0.path', '/about')
            ->assertJsonPath('data.1.path', '/blog/2?view=full')
            ->assertJsonPath('data.2.path', '/blog/10');

        $this->getJson(cp_route('utilities.cache.static-pages', ['sort' => 'path', 'order' => 'desc']))
            ->assertOk()
            ->assertJsonPath('data.0.path', '/blog/10')
            ->assertJsonPath('data.2.path', '/about');
    }

    #[Test]
    public function it_exposes_available_columns_and_their_defaults()
    {
        $this->cachePages([]);

        $columns = collect($this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()->json('meta.columns'));

        $this->assertSame(['url', 'path', 'generated_at', 'site_name', 'domain', 'actions'], $columns->pluck('field')->all());
        $this->assertSame(['url', 'generated_at', 'site_name', 'actions'], $columns->where('visible', true)->pluck('field')->values()->all());
        $this->assertSame(range(0, 5), $columns->pluck('defaultOrder')->all());
        $this->assertSame($columns->pluck('visible')->all(), $columns->pluck('defaultVisibility')->all());
        $this->assertSame('date', $columns->keyBy('field')['generated_at']['fieldtype']);
        $this->assertTrue($columns->keyBy('field')['generated_at']['sortable']);
        $this->assertFalse($columns->keyBy('field')['actions']['sortable']);
    }

    #[Test]
    public function it_applies_saved_columns_and_honors_unsaved_column_changes()
    {
        $this->cachePages(['https://example.com/about']);
        User::current()->setPreference('cache.static_pages.columns', ['path', 'generated_at'])->save();

        $columns = collect($this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()->json('meta.columns'));

        $this->assertSame(['path', 'generated_at'], $columns->where('visible', true)->pluck('field')->values()->all());

        $columns = collect($this->getJson(cp_route('utilities.cache.static-pages', ['columns' => 'domain,path,actions']))
            ->assertOk()->json('meta.columns'));

        $this->assertSame(['domain', 'path', 'actions'], $columns->where('visible', true)->pluck('field')->values()->all());
        $this->assertFalse($columns->keyBy('field')['domain']['defaultVisibility']);
        $this->assertSame(0, $columns->keyBy('field')['url']['defaultOrder']);
    }

    #[Test]
    public function it_previews_wildcards_without_invalidating_any_pages()
    {
        $cacher = $this->cachePages([
            'https://example.com/blog/one',
            'https://example.com/blog/one?sort=title',
            'https://example.com/blog/two',
            'https://example.com/about',
        ]);

        $this->postJson(cp_route('utilities.cache.preview-static-pages'), ['urls' => ['/blog/*', '/blog/one']])
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonCount(3, 'data');

        $this->assertCount(4, $cacher->getUrls('https://example.com'));
    }

    #[Test]
    public function it_limits_preview_rows_but_reports_the_full_match_count()
    {
        $this->cachePages(collect(range(1, 20))->map(fn ($page) => 'https://example.com/blog/'.$page)->all());

        $this->postJson(cp_route('utilities.cache.preview-static-pages'), ['urls' => ['/blog/*']])
            ->assertOk()
            ->assertJsonPath('total', 20)
            ->assertJsonCount(10, 'data');
    }

    #[Test]
    public function it_invalidates_an_individual_url_and_its_query_string_variants()
    {
        $cacher = $this->cachePages([
            'https://example.com/blog/one',
            'https://example.com/blog/one?sort=title',
            'https://example.com/blog/one-more',
            'https://example.de/blog/one',
        ]);

        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), ['urls' => ['https://example.com/blog/one']])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('message', fn ($message) => str_starts_with($message, 'Cleared 2 cached pages in '));

        $this->assertSame(['/blog/one-more'], $cacher->getUrls('https://example.com')->values()->all());
        $this->assertSame(['/blog/one'], $cacher->getUrls('https://example.de')->values()->all());
    }

    #[Test]
    public function it_invalidates_selected_urls_on_multiple_domains()
    {
        $cacher = $this->cachePages([
            'https://example.com/blog/one',
            'https://example.com/blog/two',
            'https://example.de/blog/one',
        ]);

        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), [
            'urls' => ['https://example.com/blog/one', 'https://example.de/blog/one'],
        ])->assertOk()->assertJsonPath('count', 2);

        $this->assertSame(['/blog/two'], $cacher->getUrls('https://example.com')->values()->all());
        $this->assertCount(0, $cacher->getUrls('https://example.de'));
    }

    #[Test]
    public function it_resolves_relative_patterns_against_the_chosen_site()
    {
        $cacher = $this->cachePages([
            'https://example.com/blog/one',
            'https://example.com/fr/blog/one',
            'https://example.com/fr/about',
            'https://example.de/blog/one',
        ]);

        $input = ['urls' => ['/blog/*'], 'site' => 'fr'];

        $this->postJson(cp_route('utilities.cache.preview-static-pages'), $input)
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.url', 'https://example.com/fr/blog/one');

        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), $input)
            ->assertOk()->assertJsonPath('count', 1);

        $this->assertSame(['/blog/one', '/fr/about'], $cacher->getUrls('https://example.com')->values()->all());
        $this->assertCount(1, $cacher->getUrls('https://example.de'));
    }

    #[Test]
    public function it_ignores_blank_lines_and_deduplicates_overlapping_matches()
    {
        $this->cachePages(['https://example.com/blog/one', 'https://example.com/blog/two']);

        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), [
            'urls' => [' /blog/* ', '', '  ', '/blog/one', '/blog/*'],
        ])->assertOk()->assertJsonPath('count', 2);

        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), ['urls' => ['/blog/*']])
            ->assertOk()->assertJsonPath('count', 0);
    }

    #[Test]
    public function it_rejects_invalid_patterns_and_empty_input_without_clearing_the_homepage()
    {
        $cacher = $this->cachePages(['https://example.com/', 'https://example.com/blog/one']);

        foreach ([[''], ['  '], [], [null], [42], ['/bl*g/*']] as $urls) {
            $this->postJson(cp_route('utilities.cache.preview-static-pages'), ['urls' => $urls])->assertUnprocessable();
            $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), ['urls' => $urls])->assertUnprocessable();
        }

        $this->assertCount(2, $cacher->getUrls('https://example.com'));
    }

    #[Test]
    public function it_requires_cache_utility_permission_for_explorer_endpoints()
    {
        $this->cachePages(['https://example.com/blog/one']);
        $this->actingAs(tap(User::make()->set('permissions', ['access cp']))->save());

        $this->getJson(cp_route('utilities.cache.static-pages'))->assertForbidden();
        $this->postJson(cp_route('utilities.cache.preview-static-pages'), ['urls' => ['/blog/*']])->assertForbidden();
        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), ['urls' => ['/blog/*']])->assertForbidden();
        $this->postJson(cp_route('utilities.cache.warm-static'))->assertForbidden();
        $this->postJson(cp_route('utilities.cache.cache-static-page'), ['url' => 'https://example.com/blog/one'])->assertForbidden();
    }

    #[Test]
    public function it_passes_explorer_endpoints_and_sites_to_the_cache_page()
    {
        $this->cachePages(['https://example.com/blog/one']);

        $this->get(cp_route('utilities.cache'))->assertInertia(fn (AssertableInertia $page) => $page
            ->where('staticPagesUrl', cp_route('utilities.cache.static-pages'))
            ->where('cacheStaticPageUrl', cp_route('utilities.cache.cache-static-page'))
            ->where('previewStaticPagesUrl', cp_route('utilities.cache.preview-static-pages'))
            ->where('warmStaticUrl', cp_route('utilities.cache.warm-static'))
            ->where('cacheSites.1', ['value' => 'fr', 'label' => 'French'])
            ->where('static.count', 1)
        );
    }

    #[Test]
    public function it_reports_invalidation_counts_through_inertia_toasts()
    {
        $this->cachePages(['https://example.com/blog/one']);
        $url = cp_route('utilities.cache');

        $this->from($url)->post(cp_route('utilities.cache.invalidate-static-pages'), ['urls' => ['/blog/*']])
            ->assertRedirect($url)
            ->assertSessionHas('success', fn ($message) => str_starts_with($message, 'Cleared 1 cached page in '));
    }

    #[Test]
    public function it_previews_and_invalidates_files_using_the_file_cache_driver()
    {
        $disk = Storage::fake('static-pages');
        $urls = ['https://example.com/blog/one', 'https://example.com/blog/two', 'https://example.com/about'];
        $cacher = $this->cachePages($urls, ['driver' => 'file', 'path' => $disk->path('pages')]);

        foreach ($urls as $url) {
            $cacher->cachePage(Request::create($url), '<h1>Cached page</h1>');
            $this->assertFileExists($cacher->getFilePath($url));
        }

        $this->postJson(cp_route('utilities.cache.preview-static-pages'), ['urls' => ['/blog/*']])
            ->assertOk()->assertJsonPath('total', 2);

        $this->postJson(cp_route('utilities.cache.invalidate-static-pages'), ['urls' => ['/blog/*']])
            ->assertOk()->assertJsonPath('count', 2);

        $this->assertFileDoesNotExist($cacher->getFilePath($urls[0]));
        $this->assertFileDoesNotExist($cacher->getFilePath($urls[1]));
        $this->assertFileExists($cacher->getFilePath($urls[2]));
    }

    #[Test]
    public function it_validates_filter_pagination_and_sort_parameters()
    {
        $this->cachePages(['https://example.com/blog/one']);

        foreach ([
            ['site' => 'missing'],
            ['page' => 0],
            ['perPage' => 101],
            ['search' => ['invalid']],
            ['sort' => 'missing'],
            ['sort' => 'actions'],
            ['order' => 'invalid'],
            ['columns' => ['url']],
        ] as $input) {
            $this->getJson(cp_route('utilities.cache.static-pages', $input))->assertUnprocessable();
        }

        $this->postJson(cp_route('utilities.cache.preview-static-pages'), ['urls' => ['/blog/*'], 'site' => 'missing'])
            ->assertUnprocessable()->assertJsonValidationErrors('site');
    }

    #[Test]
    public function it_queues_static_warming_using_the_existing_command_and_configured_queue()
    {
        $this->cachePages([]);
        Collection::make('pages')->routes('{slug}')->save();
        EntryFactory::collection('pages')->locale('en')->slug('about')->create();

        config([
            'queue.default' => 'sync',
            'statamic.static_caching.warm_queue_connection' => 'redis',
            'statamic.static_caching.warm_queue' => 'cache-warming',
        ]);

        Queue::fake();

        $this->from(cp_route('utilities.cache'))->post(cp_route('utilities.cache.warm-static'))
            ->assertRedirect(cp_route('utilities.cache'))
            ->assertSessionHas('success', 'Static cache warming queued.');

        Queue::assertPushed(StaticWarmJob::class, fn ($job) => $job->request->getUri()->getPath() === '/about'
            && $job->connection === 'redis'
            && $job->queue === 'cache-warming'
        );
    }

    #[Test]
    public function it_warms_synchronously_when_the_configured_connection_uses_the_sync_driver()
    {
        $this->cachePages([]);

        config([
            'statamic.static_caching.warm_queue_connection' => 'inline',
            'queue.connections.inline.driver' => 'sync',
        ]);

        Artisan::swap($kernel = Mockery::mock(ConsoleKernel::class));
        $kernel->shouldReceive('call')->once()->with('statamic:static:warm', ['--queue' => false])->andReturn(0);

        $this->from(cp_route('utilities.cache'))->post(cp_route('utilities.cache.warm-static'))
            ->assertRedirect(cp_route('utilities.cache'))
            ->assertSessionHas('success', fn ($message) => str_starts_with($message, 'Static cache warmed in '));
    }

    #[Test]
    public function it_reports_an_error_when_the_warming_command_fails()
    {
        $this->cachePages([]);

        Artisan::swap($kernel = Mockery::mock(ConsoleKernel::class));
        $kernel->shouldReceive('call')->once()->with('statamic:static:warm', ['--queue' => false])->andReturn(1);

        $this->post(cp_route('utilities.cache.warm-static'))
            ->assertSessionHas('error', 'Unable to warm the static cache.')
            ->assertSessionMissing('success');
    }

    #[Test]
    public function it_does_not_warm_when_static_caching_is_disabled()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());
        config(['statamic.static_caching.strategy' => null]);

        $this->post(cp_route('utilities.cache.warm-static'))
            ->assertSessionHas('error', 'Static caching is not enabled.')
            ->assertSessionMissing('success');
    }

    #[Test]
    public function it_records_when_application_cached_pages_are_generated_and_regenerated()
    {
        $url = 'https://example.com/blog/one';
        $cacher = $this->cachePages([$url]);
        $this->freezeTime();
        $generated = now()->utc()->toIso8601String();
        $request = Request::create($url);
        $response = response('Cached content');

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()
            ->assertJsonPath('data.0.generated_at', $generated);

        $this->travel(2)->minutes();
        $regenerated = now()->utc()->toIso8601String();
        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()
            ->assertJsonPath('data.0.generated_at', $regenerated);
    }

    #[Test]
    public function it_reports_unknown_generation_dates_for_existing_application_cache_entries()
    {
        $url = 'https://example.com/blog/one';
        $this->cachePages([$url]);

        StaticCache::cacheStore()->forever('static-cache:responses:'.md5($url), [
            'content' => 'Legacy cached content',
            'headers' => [],
            'status' => 200,
        ]);

        $this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()
            ->assertJsonPath('data.0.generated_at', null);
    }

    #[Test]
    public function it_reads_file_generation_dates_from_the_correct_site_path()
    {
        $disk = Storage::fake('static-dates');
        $url = 'https://example.com/fr/blog/one';
        $cacher = $this->cachePages([$url, 'https://example.com/missing'], [
            'driver' => 'file',
            'path' => [
                'en' => $disk->path('en'),
                'fr' => $disk->path('fr'),
                'de' => $disk->path('de'),
            ],
        ]);

        $path = $cacher->getFilePath($url, 'fr');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'Cached content');
        $generated = now()->subHours(2)->startOfSecond();
        touch($path, $generated->getTimestamp());
        clearstatcache(true, $path);

        $this->getJson(cp_route('utilities.cache.static-pages'))
            ->assertOk()
            ->assertJsonPath('data.0.generated_at', $generated->utc()->toIso8601String())
            ->assertJsonPath('data.1.generated_at', null);
    }
}
