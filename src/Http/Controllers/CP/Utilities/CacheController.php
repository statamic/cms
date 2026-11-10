<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use League\Glide\Server;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\StaticCache;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Imaging\CachedImages;
use Statamic\StaticCaching\CachedPages;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Statamic\StaticCaching\RecacheToken;
use Statamic\StaticCaching\WarmableUrls;
use Statamic\Support\Str;

use function Statamic\trans as __;
use function Statamic\trans_choice;

class CacheController extends CpController
{
    public function index()
    {
        return Inertia::render('utilities/Cache', [
            'stache' => $this->getStacheStats(),
            'cache' => $this->getApplicationCacheStats(),
            'static' => $this->getStaticCacheStats(),
            'staticCoverage' => Inertia::defer(fn () => config('statamic.static_caching.strategy')
                ? app(CachedPages::class)->coverage()
                : null),
            'images' => Inertia::defer(fn () => $this->getImageCacheStats()),
            'cacheSites' => Site::all()->map(fn ($site) => ['value' => $site->handle(), 'label' => $site->name()])->values(),
            'staticPagesUrl' => cp_route('utilities.cache.static-pages'),
            'cacheStaticPageUrl' => cp_route('utilities.cache.cache-static-page'),
            'previewStaticPagesUrl' => cp_route('utilities.cache.preview-static-pages'),
            'imageContainersUrl' => cp_route('utilities.cache.image-containers'),
            'clearImageContainerUrl' => cp_route('utilities.cache.clear-image-container'),
            'imageAssetsUrl' => cp_route('utilities.cache.image-assets'),
            'clearImageAssetUrl' => cp_route('utilities.cache.clear-image-asset'),
            'imageVariantsUrl' => cp_route('utilities.cache.image-variants'),
            'clearImageVariantUrl' => cp_route('utilities.cache.clear-image-variant'),
            'clearAllUrl' => cp_route('utilities.cache.clear-all'),
            'clearStacheUrl' => cp_route('utilities.cache.clear-stache'),
            'warmStacheUrl' => cp_route('utilities.cache.warm-stache'),
            'clearStaticUrl' => cp_route('utilities.cache.clear-static'),
            'warmStaticUrl' => cp_route('utilities.cache.warm-static'),
            'invalidatePagesUrl' => cp_route('utilities.cache.invalidate-static-pages'),
            'clearApplicationUrl' => cp_route('utilities.cache.clear-application-cache'),
            'clearImageUrl' => cp_route('utilities.cache.clear-image-cache'),
        ]);
    }

    protected function getStacheStats()
    {
        $size = Stache::fileSize();
        $time = Stache::buildTime();
        $built = Stache::buildDate();

        return [
            'records' => Stache::fileCount(),
            'size' => $size ? Str::fileSizeForHumans($size) : null,
            'time' => $time === null ? null : Str::timeForHumans($time),
            'rebuilt' => $built?->diffForHumans(),
        ];
    }

    protected function getApplicationCacheStats()
    {
        $driver = config('cache.default');
        $driver = ($driver === 'statamic') ? 'file (statamic)' : $driver;

        return compact('driver');
    }

    protected function getImageCacheStats()
    {
        $stats = app(CachedImages::class)->summary();

        return [
            'count' => $stats['count'],
            'size' => Str::fileSizeForHumans($stats['size']),
            'assets' => $stats['assets'],
        ];
    }

    protected function getStaticCacheStats()
    {
        $strategy = config('statamic.static_caching.strategy');
        $cacher = StaticCache::driver();

        $count = $cacher instanceof AbstractCacher
            ? $cacher->getDomains()->sum(fn ($domain) => $cacher->getUrls($domain)->count())
            : $cacher->getUrls()->count();

        return [
            'enabled' => (bool) $strategy,
            'strategy' => $strategy ?? __('Disabled'),
            'count' => $count,
        ];
    }

    public function clearAll(Request $request)
    {
        $started = microtime(true);

        $this->clearStacheCache();
        $this->clearStaticCache();
        $this->clearApplicationCache();
        $this->clearImageCache();

        return back()->withSuccess(__('All caches cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearStacheCache()
    {
        $started = microtime(true);

        Stache::clear();

        return back()->withSuccess(__('Stache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearStaticCache()
    {
        $started = microtime(true);

        StaticCache::flush();

        return back()->withSuccess(__('Static page cache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function warmStaticCache()
    {
        if (! config('statamic.static_caching.strategy')) {
            return back()->withError(__('Static caching is not enabled.'));
        }

        $started = microtime(true);
        $connection = config('statamic.static_caching.warm_queue_connection') ?? config('queue.default');
        $queued = ! in_array(config("queue.connections.{$connection}.driver", $connection), ['sync', 'null'], true);

        if (Artisan::call('statamic:static:warm', ['--queue' => $queued]) !== 0) {
            return back()->withError(__('Unable to warm the static cache.'));
        }

        return back()->withSuccess($queued
            ? __('Static cache warming queued.')
            : __('Static cache warmed in :time.', ['time' => $this->operationTime($started)]));
    }

    public function staticPages(Request $request, CachedPages $cachedPages)
    {
        $filters = $request->validate([
            'status' => ['sometimes', Rule::in(['cached', 'uncached'])],
            'site' => ['nullable', Rule::in(Site::all()->keys()->all())],
            'domain' => ['nullable', 'string', 'max:2048'],
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', Rule::in($request->input('status') === 'uncached'
                ? ['url', 'path', 'site_name', 'domain']
                : ['url', 'path', 'generated_at', 'site_name', 'domain'])],
            'order' => ['sometimes', Rule::in(['asc', 'desc'])],
            'columns' => ['nullable', 'string', 'max:255'],
        ]);

        $uncached = ($filters['status'] ?? 'cached') === 'uncached';
        $all = $uncached ? $cachedPages->uncached() : $cachedPages->all();
        $pages = $cachedPages->filter($all, $filters);
        $sort = $filters['sort'] ?? ($uncached ? 'url' : 'generated_at');
        $pages = $cachedPages->sort($pages, $sort, $filters['order'] ?? ($uncached ? 'asc' : 'desc'));
        $perPage = $filters['perPage'] ?? 15;
        $page = $filters['page'] ?? 1;
        $items = $pages->forPage($page, $perPage)->values();

        if (! $uncached && $sort !== 'generated_at') {
            $items = $cachedPages->withGeneratedAt($items);
        }

        $paginator = new LengthAwarePaginator($items, $pages->count(), $perPage, $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'domains' => $all->pluck('domain')->unique()->sort()->values(),
                'columns' => $this->staticPageColumns($request),
            ],
        ];
    }

    public function cacheStaticPage(Request $request, CachedPages $cachedPages, WarmableUrls $warmableUrls, Client $client)
    {
        abort_unless(config('statamic.static_caching.strategy'), 422, __('Static caching is not enabled.'));

        $input = $request->validate([
            'url' => ['required', 'string', 'max:2048', Rule::in($warmableUrls->all()->all())],
        ]);
        $url = $input['url'];

        if ($cachedPages->isCached($url)) {
            return ['message' => __('Page is already cached.'), 'url' => $url];
        }

        $started = microtime(true);
        $target = config('statamic.static_caching.background_recache', false)
            ? RecacheToken::addToUrl($url)
            : $url;

        try {
            $response = $client->get($target, [
                'verify' => ! config('statamic.static_caching.warm_insecure') && ! app()->isLocal(),
                'allow_redirects' => false,
                'timeout' => 30,
            ]);
        } catch (GuzzleException) {
            return response()->json(['message' => __('Unable to cache this page.')], 502);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || ! $cachedPages->isCached($url)) {
            return response()->json(['message' => __('The page could not be cached.')], 422);
        }

        return [
            'message' => __('Page cached in :time.', ['time' => $this->operationTime($started)]),
            'url' => $url,
        ];
    }

    protected function staticPageColumns(Request $request): Columns
    {
        $uncached = $request->input('status') === 'uncached';

        return (new Columns([
            Column::make('url')->label(__('URL')),
            Column::make('path')->label(__('Path'))->visible(false)->defaultVisibility(false),
            Column::make('generated_at')->label(__('Generated'))->fieldtype('date'),
            Column::make('site_name')->label(__('Site')),
            Column::make('domain')->label(__('Domain'))->visible(false)->defaultVisibility(false),
            Column::make('actions')->label(__('Actions'))->sortable(false),
        ]))
            ->reject(fn ($column) => $uncached && $column->field() === 'generated_at')
            ->values()
            ->map(fn ($column, $index) => $column->defaultOrder($index))
            ->setPreferred($request->filled('columns')
                ? explode(',', $request->input('columns'))
                : ($uncached ? 'cache.uncached_pages.columns' : 'cache.static_pages.columns'));
    }

    public function previewStaticPages(Request $request, CachedPages $cachedPages)
    {
        $input = $this->validateStaticUrls($request);
        $matches = $cachedPages->matching($cachedPages->all(), $input['urls'], $input['site'] ?? null);
        $matches = $cachedPages->filter($matches, $input);

        return ['data' => $matches->take(10)->values(), 'total' => $matches->count()];
    }

    public function invalidateStaticUrls(Request $request, CachedPages $cachedPages)
    {
        $started = microtime(true);
        $input = $this->validateStaticUrls($request);
        $matches = $cachedPages->matching($cachedPages->all(), $input['urls'], $input['site'] ?? null);
        $matches = $cachedPages->filter($matches, $input);
        $count = $cachedPages->invalidate($matches);

        $message = trans_choice('statamic::messages.cache_utility_invalidated', $count, [
            'time' => $this->operationTime($started),
        ]);

        return $request->expectsJson()
            ? ['message' => $message, 'count' => $count]
            : back()->withSuccess($message);
    }

    protected function validateStaticUrls(Request $request): array
    {
        if (is_array($urls = $request->input('urls'))) {
            $request->merge([
                'urls' => collect($urls)
                    ->map(fn ($url) => is_string($url) ? trim($url) : $url)
                    ->reject(fn ($url) => $url === '' || $url === null)
                    ->values()->all(),
            ]);
        }

        return $request->validate([
            'urls' => ['required', 'array', 'min:1', 'max:1000'],
            'urls.*' => ['required', 'string', 'max:2048', 'regex:/^[^*]*\\*?$/'],
            'site' => ['nullable', Rule::in(Site::all()->keys()->all())],
            'domain' => ['nullable', 'string', 'max:2048'],
        ], [
            'urls.*.regex' => __('Use a single wildcard at the end of a URL, such as /blog/*.'),
        ]);
    }

    public function clearApplicationCache()
    {
        $started = microtime(true);

        Artisan::call('cache:clear');

        // TODO: Stache doesn't appear to be clearing?
        // Maybe related to https://github.com/statamic/three-cms/issues/149

        return back()->withSuccess(__('Application cache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearImageCache()
    {
        $started = microtime(true);

        Artisan::call('statamic:glide:clear');

        return back()->withSuccess(__('Image cache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function imageContainers(Request $request, CachedImages $cachedImages)
    {
        return $this->imageListing($request, $cachedImages);
    }

    public function imageAssets(Request $request, CachedImages $cachedImages)
    {
        return $this->imageListing($request, $cachedImages, $this->imageCacheContainer($request));
    }

    public function imageVariants(Request $request, CachedImages $cachedImages)
    {
        $asset = $this->imageCacheAsset($request, $cachedImages);
        $input = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', Rule::in(['title', 'format', 'size', 'generated_at', 'hash'])],
            'order' => ['sometimes', Rule::in(['asc', 'desc'])],
            'columns' => ['nullable', 'string', 'max:255'],
        ]);

        $sort = $input['sort'] ?? 'size';
        $sortOptions = $sort === 'size' ? SORT_REGULAR : SORT_NATURAL | SORT_FLAG_CASE;
        $descending = ($input['order'] ?? 'desc') === 'desc';
        $variants = $cachedImages->variants($asset)
            ->when($input['search'] ?? null, function ($variants, $search) {
                return $variants->filter(fn ($variant) => Str::contains(
                    Str::lower($variant['title'].' '.$variant['format'].' '.$variant['hash']),
                    Str::lower($search)
                ));
            })
            ->sortBy($sort, $sortOptions, $descending)
            ->values();
        $perPage = $input['perPage'] ?? 15;
        $page = $input['page'] ?? 1;
        $paginator = new LengthAwarePaginator(
            $variants->forPage($page, $perPage)->values(),
            $variants->count(),
            $perPage,
            $page
        );

        return [
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'columns' => (new Columns([
                    Column::make('preview')->label(__('Preview'))->sortable(false),
                    Column::make('dimensions')->label(__('Dimensions'))->sortable(false),
                    Column::make('format')->label(__('Format')),
                    Column::make('size')->label(__('Size'))->numeric(true),
                    Column::make('generated_at')->label(__('Generated'))->fieldtype('date'),
                    Column::make('actions')->label(__('Actions'))->sortable(false),
                    Column::make('title')->label(__('Filename'))->visible(false)->defaultVisibility(false),
                    Column::make('hash')->label(__('Hash'))->visible(false)->defaultVisibility(false),
                ]))
                    ->map(fn ($column, $index) => $column->defaultOrder($index)),
            ],
        ];
    }

    public function imagePreview(Request $request, Server $server)
    {
        $container = $this->imageCacheContainer($request);
        $this->authorize('view', $container);

        $input = $request->validate(['path' => ['required', 'string', 'max:8192']]);
        $path = $input['path'];
        $segments = explode('/', $path);

        abort_unless(str_starts_with($path, 'containers/'.$container->id().'/')
            && ! str_contains($path, '\\')
            && ! str_contains($path, "\0")
            && ! in_array('..', $segments)
            && ! in_array('.', $segments), 404);

        $cache = $server->getCache();
        abort_unless($cache->fileExists($path), 404);
        abort_unless(in_array($cache->mimeType($path), [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/tiff',
        ]), 415);

        $response = $server->getResponseFactory()->create($cache, $path);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->remove('Expires');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    protected function imageListing(Request $request, CachedImages $cachedImages, ?AssetContainerContract $container = null)
    {
        $pathColumn = $container ? 'path' : 'handle';
        $input = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', Rule::in(['title', $pathColumn, 'count', 'size'])],
            'order' => ['sometimes', Rule::in(['asc', 'desc'])],
            'columns' => ['nullable', 'string', 'max:255'],
        ]);

        $sort = $input['sort'] ?? 'size';
        $sortOptions = in_array($sort, ['count', 'size']) ? SORT_REGULAR : SORT_NATURAL | SORT_FLAG_CASE;
        $descending = ($input['order'] ?? 'desc') === 'desc';
        $all = $container ? $cachedImages->assets($container) : $cachedImages->containers();
        $items = $all
            ->when($input['search'] ?? null, function ($items, $search) use ($pathColumn) {
                return $items->filter(fn ($item) => Str::contains(
                    Str::lower($item['title'].' '.$item[$pathColumn]),
                    Str::lower($search)
                ));
            })
            ->sortBy($sort, $sortOptions, $descending)
            ->values();

        $perPage = $input['perPage'] ?? 15;
        $page = $input['page'] ?? 1;
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page
        );

        return [
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'container_count' => $container ? null : $all->count(),
                'single_container' => ! $container && $all->count() === 1 ? $all->first() : null,
                'columns' => (new Columns([
                    Column::make($container ? 'path' : 'title')->label($container ? __('Asset') : __('Container')),
                    Column::make($container ? 'title' : 'handle')
                        ->label($container ? __('Filename') : __('Handle'))
                        ->visible(false)->defaultVisibility(false),
                    Column::make('count')->label(__('Cached images'))->numeric(true),
                    Column::make('size')->label(__('Size'))->numeric(true),
                    Column::make('actions')->label(__('Actions'))->sortable(false),
                ]))
                    ->map(fn ($column, $index) => $column->defaultOrder($index))
                    ->setPreferred($request->filled('columns')
                        ? explode(',', $request->input('columns'))
                        : ($container ? 'cache.image_assets.columns' : 'cache.image_containers.columns')),
            ],
        ];
    }

    public function clearImageContainer(Request $request, CachedImages $cachedImages)
    {
        $started = microtime(true);
        $handles = $this->imageCacheSelections($request, 'container', 'containers', AssetContainer::all()->map->handle()->all());
        $containers = collect($handles)->map(fn ($handle) => AssetContainer::find($handle));
        $results = $containers->map(fn ($container) => $cachedImages->clear($container));
        $result = ['count' => $results->sum('count'), 'size' => $results->sum('size')];
        $source = $containers->count() === 1
            ? $containers->first()->title()
            : __(':count containers', ['count' => $containers->count()]);

        return $result + [
            'message' => trans_choice('statamic::messages.cache_utility_images_cleared', $result['count'], [
                'container' => $source,
                'size' => Str::fileSizeForHumans($result['size']),
                'time' => $this->operationTime($started),
            ]),
        ];
    }

    public function clearImageAsset(Request $request, CachedImages $cachedImages)
    {
        $started = microtime(true);
        $container = $this->imageCacheContainer($request);
        $ids = $this->imageCacheSelections($request, 'asset', 'assets', $cachedImages->assets($container)->pluck('id')->all());
        $assets = collect($ids)->map(fn ($id) => $container->makeAsset(Str::after($id, $container->id().'::')));
        $results = $assets->map(fn ($asset) => $cachedImages->clearAsset($asset));
        $result = ['count' => $results->sum('count'), 'size' => $results->sum('size')];
        $source = $assets->count() === 1
            ? $assets->first()->path()
            : __(':count assets', ['count' => $assets->count()]);

        return $result + [
            'message' => trans_choice('statamic::messages.cache_utility_asset_images_cleared', $result['count'], [
                'asset' => $source,
                'size' => Str::fileSizeForHumans($result['size']),
                'time' => $this->operationTime($started),
            ]),
        ];
    }

    public function clearImageVariant(Request $request, CachedImages $cachedImages)
    {
        $started = microtime(true);
        $asset = $this->imageCacheAsset($request, $cachedImages);
        $paths = $this->imageCacheSelections($request, 'variant', 'variants', $cachedImages->variants($asset)->pluck('id')->all());
        $result = $cachedImages->clearVariants($asset, $paths);

        return $result + [
            'message' => trans_choice('statamic::messages.cache_utility_asset_images_cleared', $result['count'], [
                'asset' => $asset->path(),
                'size' => Str::fileSizeForHumans($result['size']),
                'time' => $this->operationTime($started),
            ]),
        ];
    }

    protected function imageCacheSelections(Request $request, string $single, string $multiple, array $allowed): array
    {
        $input = $request->validate([
            $single => ['required_without:'.$multiple, 'prohibits:'.$multiple, 'string', Rule::in($allowed)],
            $multiple => ['required_without:'.$single, 'prohibits:'.$single, 'array', 'min:1', 'max:1000'],
            $multiple.'.*' => ['required', 'string', 'distinct', Rule::in($allowed)],
        ]);

        return $input[$multiple] ?? [$input[$single]];
    }

    protected function imageCacheAsset(Request $request, CachedImages $cachedImages): AssetContract
    {
        $container = $this->imageCacheContainer($request);
        $input = $request->validate([
            'asset' => ['required', 'string', 'max:4096', Rule::in($cachedImages->assets($container)->pluck('id')->all())],
        ]);

        return $container->makeAsset(Str::after($input['asset'], $container->id().'::'));
    }

    protected function imageCacheContainer(Request $request): AssetContainerContract
    {
        $input = $request->validate([
            'container' => ['required', 'string', Rule::in(AssetContainer::all()->map->handle()->all())],
        ]);

        return AssetContainer::find($input['container']);
    }

    public function warmStacheCache()
    {
        $started = microtime(true);

        Stache::warm();

        return back()->withSuccess(__('Stache warmed in :time.', ['time' => $this->operationTime($started)]));
    }

    protected function operationTime(float $started): string
    {
        return Str::timeForHumans((int) round((microtime(true) - $started) * 1000));
    }
}
