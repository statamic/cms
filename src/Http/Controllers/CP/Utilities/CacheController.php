<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use League\Glide\Server;
use Statamic\Facades\Stache;
use Statamic\Facades\StaticCache;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class CacheController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.cache', [
            'stache' => $this->getStacheStats(),
            'cache' => $this->getApplicationCacheStats(),
            'static' => $this->getStaticCacheStats(),
            'images' => $this->getImageCacheStats(),
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

        return $this->$method();
    }

    protected function clearAllCache()
    {
        $this->clearStacheCache();
        $this->clearStaticCache();
        $this->clearApplicationCache();
        $this->clearImageCache();

        return back()->withSuccess(__('All caches cleared.'));
    }

    protected function clearStacheCache()
    {
        Stache::refresh();

        return back()->withSuccess(__('Stache cleared.'));
    }

    protected function clearStaticCache()
    {
        StaticCache::flush();

        return back()->withSuccess(__('Static page cache cleared.'));
    }

    protected function clearApplicationCache()
    {
        Artisan::call('cache:clear');

        // TODO: Stache doesn't appear to be clearing?
        // Maybe related to https://github.com/statamic/three-cms/issues/149

        return back()->withSuccess(__('Application cache cleared.'));
    }

    protected function clearImageCache()
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
