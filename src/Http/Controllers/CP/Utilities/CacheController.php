<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use League\Glide\Server;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\StaticCache;
use Statamic\Facades\URL;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Statamic\Support\Str;

use function Statamic\trans as __;

class CacheController extends CpController
{
    public function index(Request $request)
    {
        return Inertia::render('utilities/Cache', [
            'stache' => $this->getStacheStats(),
            'cache' => $this->getApplicationCacheStats(),
            'static' => $this->getStaticCacheStats(),
            'images' => Inertia::defer(fn () => $this->getImageCacheStats()),
            'operationResult' => $request->session()->pull('cacheOperation'),
            'clearAllUrl' => cp_route('utilities.cache.clear-all'),
            'clearStacheUrl' => cp_route('utilities.cache.clear-stache'),
            'warmStacheUrl' => cp_route('utilities.cache.warm-stache'),
            'clearStaticUrl' => cp_route('utilities.cache.clear-static'),
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
        $files = collect(app(Server::class)->getCache()->listContents('', true))
            ->filter(function ($file) {
                return $file['type'] === 'file';
            });

        return [
            'count' => $files->count(),
            'size' => Str::fileSizeForHumans($files->reduce(function ($size, $file) {
                return $size + $file->fileSize();
            }, 0)),
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

        return $this->operationResponse(__('All caches cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearStacheCache()
    {
        $started = microtime(true);

        Stache::refresh();

        return $this->operationResponse(__('Stache rebuilt in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearStaticCache()
    {
        $started = microtime(true);

        StaticCache::flush();

        return $this->operationResponse(__('Static page cache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function invalidateStaticUrls(Request $request)
    {
        $started = microtime(true);

        $request->validate([
            'urls' => ['required', 'array'],
        ]);

        $urls = $request->collect('urls');

        $absoluteUrls = $urls->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $urls
            ->reject(fn (string $rule) => URL::isAbsolute($rule))
            ->map(fn (string $rule) => URL::tidy(Site::selected()->url().'/'.$rule, withTrailingSlash: false))
            ->all();

        $urls = [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];

        app(Cacher::class)->invalidateUrls($urls);

        return $this->operationResponse(__('Static cache URLs invalidated in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearApplicationCache()
    {
        $started = microtime(true);

        Artisan::call('cache:clear');

        // TODO: Stache doesn't appear to be clearing?
        // Maybe related to https://github.com/statamic/three-cms/issues/149

        return $this->operationResponse(__('Application cache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function clearImageCache()
    {
        $started = microtime(true);

        Artisan::call('statamic:glide:clear');

        return $this->operationResponse(__('Image cache cleared in :time.', ['time' => $this->operationTime($started)]));
    }

    public function warmStacheCache()
    {
        $started = microtime(true);

        Stache::warm();

        return $this->operationResponse(__('Stache warmed in :time.', ['time' => $this->operationTime($started)]));
    }

    protected function operationTime(float $started): string
    {
        return Str::timeForHumans((int) round((microtime(true) - $started) * 1000));
    }

    protected function operationResponse(string $message)
    {
        return back()->withSuccess($message)->with('cacheOperation', $message);
    }
}
