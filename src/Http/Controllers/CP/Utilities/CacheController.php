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
use Statamic\Support\Str;

class CacheController extends CpController
{
    public function index()
    {
        return Inertia::render('utilities/Cache', [
            'stache' => $this->getStacheStats(),
            'cache' => $this->getApplicationCacheStats(),
            'static' => $this->getStaticCacheStats(),
            'images' => $this->getImageCacheStats(),
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
            'time' => $time ? Str::timeForHumans($time) : __('Refresh'),
            'rebuilt' => $built ? $built->diffForHumans() : __('Refresh'),
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

        return [
            'enabled' => (bool) $strategy,
            'strategy' => $strategy ?? __('Disabled'),
            'count' => StaticCache::driver()->getUrls()->count(),
        ];
    }

    public function clearAll(Request $request)
    {
        $this->clearStacheCache();
        $this->clearStaticCache();
        $this->clearApplicationCache();
        $this->clearImageCache();

        return back()->withSuccess(__('All caches cleared.'));
    }

    public function clearStacheCache()
    {
        Stache::refresh();

        return back()->withSuccess(__('Stache cleared.'));
    }

    public function clearStaticCache()
    {
        StaticCache::flush();

        return back()->withSuccess(__('Static page cache cleared.'));
    }

    public function invalidateStaticUrls(Request $request)
    {
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

        return back()->withSuccess(__('Invalidated URLs in the Static Cache.'));
    }

    public function clearApplicationCache()
    {
        Artisan::call('cache:clear');

        // TODO: Stache doesn't appear to be clearing?
        // Maybe related to https://github.com/statamic/three-cms/issues/149

        return back()->withSuccess(__('Application cache cleared.'));
    }

    public function clearImageCache()
    {
        Artisan::call('statamic:glide:clear');

        return back()->withSuccess(__('Image cache cleared.'));
    }

    public function warmStacheCache()
    {
        Stache::warm();

        return back()->withSuccess(__('Stache warmed.'));
    }
}
