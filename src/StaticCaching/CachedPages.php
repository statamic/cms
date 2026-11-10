<?php

namespace Statamic\StaticCaching;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\Support\Str;

class CachedPages
{
    public function __construct(private Cacher $cacher)
    {
    }

    public function all(): Collection
    {
        $domains = $this->cacher instanceof AbstractCacher
            ? $this->cacher->getDomains()
            : collect([rtrim(Site::selected()->absoluteUrl(), '/')]);

        return $domains->flatMap(function ($domain) {
            return $this->cacher->getUrls($domain)->map(function ($path) use ($domain) {
                $url = URL::isAbsolute($path) ? $path : rtrim($domain, '/').'/'.ltrim($path, '/');

                return $this->page($url, $domain);
            })->values();
        })->unique('url')->sortBy('url', SORT_NATURAL)->values();
    }

    public function coverage(): array
    {
        $urls = app(WarmableUrls::class)->all();
        $cached = $urls->filter(fn ($url) => $this->isCached($url))->count();

        return [
            'cached' => $cached,
            'total' => $urls->count(),
            'uncached' => $urls->count() - $cached,
        ];
    }

    public function uncached(): Collection
    {
        return app(WarmableUrls::class)->all()
            ->reject(fn ($url) => $this->isCached($url))
            ->map(fn ($url) => $this->page($url, Request::create($url)->getSchemeAndHttpHost()))
            ->values();
    }

    public function isCached(string $url): bool
    {
        $request = Request::create($url);

        if ($this->cacher instanceof FileCacher) {
            $normalized = $this->cacher->getUrl($request);

            return File::exists($this->cacher->getFilePath($normalized, Site::findByUrl($url)?->handle()));
        }

        return $this->cacher->hasCachedPage($request);
    }

    private function page(string $url, string $domain): array
    {
        $site = Site::findByUrl($url);
        $parts = parse_url($url);

        return [
            'id' => $url,
            'title' => $url,
            'url' => $url,
            'path' => ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : ''),
            'domain' => $domain,
            'site' => $site?->handle(),
            'site_name' => $site?->name(),
        ];
    }

    public function filter(Collection $pages, array $filters): Collection
    {
        return $pages
            ->when($filters['site'] ?? null, fn ($pages, $site) => $pages->where('site', $site))
            ->when($filters['domain'] ?? null, fn ($pages, $domain) => $pages->where('domain', $domain))
            ->when($filters['search'] ?? null, function ($pages, $search) {
                return $pages->filter(fn ($page) => Str::contains(Str::lower($page['url']), Str::lower($search)));
            })
            ->values();
    }

    public function withGeneratedAt(Collection $pages): Collection
    {
        return $pages->map(function ($page) {
            $timestamp = $this->cacher instanceof AbstractCacher
                ? $this->cacher->getGeneratedAt($page['url'])
                : null;

            $page['generated_at'] = $timestamp === null
                ? null
                : Carbon::createFromTimestampUTC($timestamp)->toIso8601String();

            return $page;
        });
    }

    public function sort(Collection $pages, string $column, string $direction): Collection
    {
        if ($column === 'generated_at') {
            $pages = $this->withGeneratedAt($pages);
        }

        return $pages->sort(function ($a, $b) use ($column, $direction) {
            $aValue = $a[$column];
            $bValue = $b[$column];

            if ($aValue === null || $bValue === null) {
                $comparison = ($aValue === null) <=> ($bValue === null);
            } else {
                $comparison = strnatcasecmp($aValue, $bValue) * ($direction === 'desc' ? -1 : 1);
            }

            return $comparison ?: strnatcasecmp($a['url'], $b['url']);
        })->values();
    }

    public function matching(Collection $pages, array $rules, ?string $site = null): Collection
    {
        $baseUrl = rtrim(($site ? Site::get($site) : Site::selected())->absoluteUrl(), '/');

        $rules = collect($rules)->map(function ($rule) use ($baseUrl) {
            return URL::isAbsolute($rule) ? $rule : $baseUrl.'/'.ltrim($rule, '/');
        });

        return $pages->filter(function ($page) use ($rules) {
            return $rules->contains(function ($rule) use ($page) {
                if (str_ends_with($rule, '*')) {
                    return str_starts_with($page['url'], substr($rule, 0, -1));
                }

                return $page['url'] === $rule || str_starts_with($page['url'], $rule.'?');
            });
        })->values();
    }

    public function invalidate(Collection $pages): int
    {
        if ($pages->isEmpty()) {
            return 0;
        }

        $this->cacher->invalidateUrls($pages->pluck('url')->all());

        return $pages->pluck('url')->diff($this->all()->pluck('url'))->count();
    }
}
