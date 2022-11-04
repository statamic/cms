<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Replacers\CsrfTokenReplacer;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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

        $this->cacheUrl($this->makeHash($url), ...$this->getPathAndDomain($url));
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
    public function invalidateUrl($url, $domain = null)
    {
        $site = optional(Site::findByUrl($domain.$url))->handle();

        $this
            ->getUrls($domain)
            ->filter(fn ($value) => $value === $url || str_starts_with($value, $url.'?'))
            ->each(function ($value, $key) use ($site, $domain) {
                $this->writer->delete($this->getFilePath($value, $site));
                $this->forgetUrl($key, $domain);
            });
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
     * @param $url
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
    var els = document.getElementsByClassName('nocache');
    var map = {};
    for (var i = 0; i < els.length; i++) {
        var section = els[i].getAttribute('data-nocache');
        map[section] = els[i];
    }

    fetch('/!/nocache', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            url: window.location.href,
            sections: Object.keys(map)
        })
    })
    .then((response) => response.json())
    .then((data) => {
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

        document.dispatchEvent(new CustomEvent('statamic:nocache.replaced'));
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
}
