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
            'clearAllUrl' => cp_route('utilities.cache.clear', 'all'),
            'clearStacheUrl' => cp_route('utilities.cache.clear', 'stache'),
            'warmStacheUrl' => cp_route('utilities.cache.warm', 'stache'),
            'clearStaticUrl' => cp_route('utilities.cache.clear', 'static'),
            'clearApplicationUrl' => cp_route('utilities.cache.clear', 'application'),
            'clearImageUrl' => cp_route('utilities.cache.clear', 'image'),
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

    public function clear(Request $request, $cache)
    {
        $method = 'clear'.ucfirst($cache).'Cache';

        return $this->$method($request);
    }

    protected function clearAllCache(Request $request)
    {
        $this->clearStacheCache($request);
        $this->clearStaticCache($request);
        $this->clearApplicationCache($request);
        $this->clearImageCache($request);

        return back()->withSuccess(__('All caches cleared.'));
    }

    protected function clearStacheCache(Request $request)
    {
        Stache::refresh();

        return back()->withSuccess(__('Stache cleared.'));
    }

    protected function clearStaticCache(Request $request)
    {
        if ($request->urls) {
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

            return back()->withSuccess(__('Invalidated URLs.'));
        }

        StaticCache::flush();

        return back()->withSuccess(__('Static page cache cleared.'));
    }

    protected function clearApplicationCache(Request $request)
    {
        Artisan::call('cache:clear');

        // TODO: Stache doesn't appear to be clearing?
        // Maybe related to https://github.com/statamic/three-cms/issues/149

        return back()->withSuccess(__('Application cache cleared.'));
    }

    protected function clearImageCache(Request $request)
    {
        Artisan::call('statamic:glide:clear');

        return back()->withSuccess(__('Image cache cleared.'));
    }

    public function warm(Request $request, $cache)
    {
        $method = 'warm'.ucfirst($cache).'Cache';

        return $this->$method();
    }

    protected function warmStacheCache()
    {
        Stache::warm();

        return back()->withSuccess(__('Stache warmed.'));
    }
}
