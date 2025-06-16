<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Page;
use Statamic\StaticCaching\Replacers\CsrfTokenReplacer;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

class FileCacher extends AbstractCacher
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var bool
     */
    private $shouldOutputJs = false;

    /**
     * @var string
     */
    private $nocacheJs;

    /**
     * @var string
     */
    private $nocachePlaceholder;

    /**
     * @var bool
     */
    private $logRewriteWarning = true;

    /**
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

        $path = $this->getFilePath($url);

        if (! $this->writer->write($path, $content, $this->config('lock_hold_length'))) {
            return;
        }

        $this->cacheUrl($this->makeHash($url), ...$this->getPathAndDomain($url));
    }

    public function preventLoggingRewriteWarning()
    {
        $this->logRewriteWarning = false;
    }

    /**
     * @return Page
     */
    public function getCachedPage(Request $request)
    {
        $url = $this->getUrl($request);

        $path = $this->getFilePath($url);

        if ($this->logRewriteWarning && ! $this->isLongQueryStringPath($path)) {
            Log::debug('Static cache loaded ['.$url.'] If you are seeing this, your server rewrite rules have not been set up correctly.');
        }

        return new Page(File::get($path));
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
    public function invalidateUrl($url, $domain = null)
    {
        $site = optional(Site::findByUrl($domain.$url))->handle();

        $this
            ->getUrls($domain)
            ->filter(fn ($value) => $value === $url || str_starts_with($value, $url.'?'))
            ->each(function ($value, $key) use ($site, $domain) {
                $this->writer->delete($this->getFilePath($domain.$value, $site));
                $this->forgetUrl($key, $domain);
            });

        $this->getFiles($site)
            ->filter(fn ($file) => str_starts_with($file, $url.'_'))
            ->each(function ($file, $path) {
                $this->writer->delete($path);
            });

        UrlInvalidated::dispatch($url, $domain);
    }

    /**
     * Get lazy collection file listing.
     *
     * @param  Site  $site
     */
    public function getFiles($site): LazyCollection
    {
        $cachePath = $this->getCachePath($site);
        if (! $cachePath || ! File::exists($cachePath)) {
            return LazyCollection::make();
        }

        $directoryIterator = new \RecursiveDirectoryIterator($cachePath, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        return LazyCollection::make(function () use ($iterator, $cachePath) {
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'html') {
                    continue;
                }

                yield $file->getPathName() => Str::start(Str::replaceFirst($cachePath, '', $file->getPathName()), '/');
            }
        });
    }

    public function getCachePaths()
    {
        $paths = $this->config('path');

        if (! is_array($paths)) {
            $paths = Site::all()->mapWithKeys(fn ($site) => [$site->handle() => $paths])->all();
        }

        return $paths;
    }

    /**
     * Get the path where static files are stored.
     *
     * @param  string|null  $site  A specific site's path.
     * @return string
     */
    public function getCachePath($site = null)
    {
        $paths = $this->getCachePaths();

        if (! $site) {
            $site = $this->config('locale');
        }

        return $paths[$site];
    }

    /**
     * Get the path to the cached file.
     *
     * @return string
     */
    public function getFilePath($url, $site = null)
    {
        $urlParts = parse_url($url);
        $pathParts = pathinfo($urlParts['path']);
        $slug = $pathParts['basename'];
        $query = $this->config('ignore_query_strings') ? '' : Arr::get($urlParts, 'query', '');

        if ($this->isBasenameTooLong($basename = $slug.'_'.$query.'.html')) {
            $basename = $slug.'_lqs_'.md5($query).'.html';
        }

        return $this->getCachePath($site).$pathParts['dirname'].'/'.$basename;
    }

    private function isBasenameTooLong($basename)
    {
        return strlen($basename) > $this->config('max_filename_length', 255);
    }

    private function isLongQueryStringPath($path)
    {
        return Str::contains($path, '_lqs_');
    }

    public function setNocacheJs(string $js)
    {
        $this->nocacheJs = $js;
    }

    public function getNocacheJs(): string
    {
        $csrfPlaceholder = CsrfTokenReplacer::REPLACEMENT;

        $default = <<<EOT
(function() {
    function createMap() {
        var map = {};
        var els = document.getElementsByClassName('nocache');
        for (var i = 0; i < els.length; i++) {
            var section = els[i].getAttribute('data-nocache');
            map[section] = els[i];
        }
        return map;
    }

    var map = createMap();

    fetch('/!/nocache', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            url: window.location.href.split('#')[0],
            sections: Object.keys(map)
        })
    })
    .then((response) => response.json())
    .then((data) => {
        map = createMap(); // Recreate map in case the DOM changed.

        const regions = data.regions;
        for (var key in regions) {
            if (map[key]) map[key].outerHTML = regions[key];
        }

        for (const input of document.querySelectorAll('input[value="$csrfPlaceholder"]')) {
            input.value = data.csrf;
        }

        for (const meta of document.querySelectorAll('meta[content="$csrfPlaceholder"]')) {
            meta.content = data.csrf;
        }

        for (const input of document.querySelectorAll('script[data-csrf="$csrfPlaceholder"]')) {
            input.setAttribute('data-csrf', data.csrf);
        }

        if (window.hasOwnProperty('livewire_token')) {
            window.livewire_token = data.csrf
        }

        if (window.hasOwnProperty('livewireScriptConfig')) {
            window.livewireScriptConfig.csrf = data.csrf
        }

        document.dispatchEvent(new CustomEvent('statamic:nocache.replaced', { detail: data }));
    });
})();
EOT;

        return $this->nocacheJs ?? $default;
    }

    public function shouldOutputJs(): bool
    {
        return $this->shouldOutputJs;
    }

    public function includeJs()
    {
        $this->shouldOutputJs = true;
    }

    public function setNocachePlaceholder(string $content)
    {
        $this->nocachePlaceholder = $content;
    }

    public function getNocachePlaceholder()
    {
        return $this->nocachePlaceholder ?? '';
    }

    public function getUrl(Request $request)
    {
        $url = $request->getUri();

        if ($this->isExcluded($url)) {
            return $url;
        }

        $url = explode('?', $url)[0];

        if ($this->config('ignore_query_strings', false)) {
            return $url;
        }

        // Symfony will normalize the query string which includes alphabetizing it. However, we
        // want to maintain the real order so that when nginx looks for the file, it can find
        // it. The following is the same normalizing code from Symfony without the ordering.

        if (! $qs = $request->server->get('QUERY_STRING')) {
            return $url;
        }

        $qs = HeaderUtils::parseQuery($qs);

        return $url.'?'.http_build_query($qs, '', '&', \PHP_QUERY_RFC3986);
    }
}
