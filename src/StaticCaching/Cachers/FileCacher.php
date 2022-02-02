<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\File;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FileCacher extends AbstractCacher
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @param  Writer  $writer
     * @param  Repository  $cache
     * @param  array  $config
     */
    public function __construct(Writer $writer, Repository $cache, $config)
    {
        parent::__construct($cache, $config);

        $this->writer = $writer;
    }

    /**
     * Cache a page.
     *
     * @param  \Illuminate\Http\Request  $request  Request associated with the page to be cached
     * @param  string  $content  The response content to be cached
     */
    public function cachePage(Request $request, $content)
    {
        $url = $this->getUrl($request);

        if ($this->isExcluded($url)) {
            return;
        }

        $content = $this->normalizeContent($content);

        $path = $this->getFilePath($request->getUri());

        if (! $this->writer->write($path, $content, $this->config('lock_hold_length'))) {
            return;
        }

        $this->cacheUrl($this->makeHash($url), $url);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function getCachedPage(Request $request)
    {
        $url = $this->getUrl($request);

        $path = $this->getFilePath($url);

        if (! $this->isLongQueryStringPath($path)) {
            Log::debug('Static cache loaded ['.$url.'] If you are seeing this, your server rewrite rules have not been set up correctly.');
        }

        return File::get($path);
    }

    public function hasCachedPage(Request $request)
    {
        $url = $this->getUrl($request);

        return File::exists($this->getFilePath($url));
    }

    /**
     * Flush out the entire static cache.
     *
     * @return void
     */
    public function flush()
    {
        foreach ($this->getCachePaths() as $path) {
            $this->writer->flush($path);
        }

        $this->flushUrls();
    }

    /**
     * Invalidate a URL.
     *
     * @param  string  $url
     * @return void
     */
    public function invalidateUrl($url)
    {
        if (! $key = $this->getUrls()->flip()->get($url)) {
            // URL doesn't exist, nothing to invalidate.
            return;
        }

        $this->writer->delete($this->getFilePath($url));

        $this->forgetUrl($key);
    }

    public function getCachePaths()
    {
        $paths = $this->config('path');

        if (! is_array($paths)) {
            $paths = [$this->config('locale') => $paths];
        }

        return $paths;
    }

    /**
     * Get the path where static files are stored.
     *
     * @param  string|null  $locale  A specific locale's path.
     * @return string
     */
    public function getCachePath($locale = null)
    {
        $paths = $this->getCachePaths();

        if (! $locale) {
            $locale = $this->config('locale');
        }

        return $paths[$locale];
    }

    /**
     * Get the path to the cached file.
     *
     * @param $url
     * @return string
     */
    public function getFilePath($url)
    {
        $urlParts = parse_url($url);
        $pathParts = pathinfo($urlParts['path']);
        $slug = $pathParts['basename'];
        $query = $this->config('ignore_query_strings') ? '' : Arr::get($urlParts, 'query', '');

        if ($this->isBasenameTooLong($basename = $slug.'_'.$query.'.html')) {
            $basename = $slug.'_lqs_'.md5($query).'.html';
        }

        return $this->getCachePath().$pathParts['dirname'].'/'.$basename;
    }

    private function isBasenameTooLong($basename)
    {
        return strlen($basename) > $this->config('max_filename_length', 255);
    }

    private function isLongQueryStringPath($path)
    {
        return Str::contains($path, '_lqs_');
    }
}
