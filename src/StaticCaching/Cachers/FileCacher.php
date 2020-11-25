<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\Facades\File;

class FileCacher extends AbstractCacher
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @param Writer $writer
     * @param Repository $cache
     * @param array $config
     */
    public function __construct(Writer $writer, Repository $cache, $config)
    {
        parent::__construct($cache, $config);

        $this->writer = $writer;
    }

    /**
     * Cache a page.
     *
     * @param \Illuminate\Http\Request $request     Request associated with the page to be cached
     * @param string                   $content     The response content to be cached
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
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getCachedPage(Request $request)
    {
        $url = $this->getUrl($request);

        \Log::debug('Static cache loaded ['.$url.'] If you are seeing this, your server rewrite rules have not been set up correctly.');

        return File::get($this->getFilePath($url));
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
     * @param string $url
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
     * @param string|null $locale  A specific locale's path.
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
        $parts = parse_url($url);

        return sprintf('%s%s_%s.html',
            $this->getCachePath(),
            $parts['path'],
            array_get($parts, 'query', '')
        );
    }
}
