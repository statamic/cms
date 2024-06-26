<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Event;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Page;
use Statamic\Support\Str;

class DatabaseCacher extends AbstractCacher
{
    private ?Page $cached = null;

    public function __construct($config)
    {
        parent::__construct(StaticCache::cacheStore(), $config);
    }

    public function cachePage(Request $request, $content)
    {
        $url = $this->getUrl($request);

        if ($this->isExcluded($url)) {
            return;
        }

        Event::listen(ResponsePrepared::class, function (ResponsePrepared $event) use ($url, $content) {
            $headers = collect($event->response->headers->all())
                ->reject(fn ($value, $key) => in_array($key, ['date', 'x-powered-by', 'cache-control', 'expires', 'set-cookie']))
                ->all();

            PageModel::create([
                'url' => $url,
                'content' => $this->normalizeContent($content),
                'headers' => $headers,
                'status' => $event->response->getStatusCode(),
            ]);
        });
    }

    public function hasCachedPage(Request $request)
    {
        return (bool) $this->cached = $this->getPage($request);
    }

    public function getCachedPage(Request $request)
    {
        return $this->cached ?? $this->getPage($request);
    }

    private function getPage(Request $request): ?Page
    {
        $url = $this->getUrl($request);

        if (! $model = PageModel::where('url', $url)->first()) {
            return null;
        }

        return new Page($model->content, $model->headers, $model->status);
    }

    public function flush()
    {
        PageModel::truncate();
    }

    public function invalidateUrls($urls)
    {
        collect($urls)->each(function ($url) {
            if (Str::contains($url, '*')) {
                $this->invalidateWildcardUrl($url);
            } else {
                $this->invalidateUrl(...$this->getPathAndDomain($url));
            }
        });
    }

    public function invalidateUrl($url, $domain = null)
    {
        PageModel::where('url', $domain.$url)->delete();

        UrlInvalidated::dispatch($url, $domain);
    }
}
