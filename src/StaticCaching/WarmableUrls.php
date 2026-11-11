<?php

namespace Statamic\StaticCaching;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\Commands\StaticWarm;
use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Support\Str;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Taxonomy;

class WarmableUrls
{
    private const CACHE_KEY = 'statamic:cache-utility:warmable-urls';

    public function all(): Collection
    {
        return collect(array_keys($this->cachedUrls()));
    }

    public function contains(string $url): bool
    {
        return isset($this->cachedUrls()[$url]);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        CachedPages::clearCache();
    }

    private function cachedUrls(): array
    {
        return Cache::remember(self::CACHE_KEY, 60, fn () => array_fill_keys($this->discover()->all(), true));
    }

    protected function discover(): Collection
    {
        $site = Site::current();
        $cacher = app(Cacher::class);

        try {
            return collect()
                ->merge($this->entries())
                ->merge($this->taxonomies())
                ->merge($this->terms($this->scopedTerms()))
                ->merge($this->customRoutes())
                ->merge(app(StaticWarm::class)->additionalUrls())
                ->filter()
                ->unique()
                ->reject(function ($url) use ($cacher, $site) {
                    Site::setCurrent((Site::findByUrl($url) ?? $site)->handle());

                    return $cacher->isExcluded($url);
                })
                ->sort()
                ->values();
        } finally {
            Site::setCurrent($site->handle());
        }
    }

    public function entries(): Collection
    {
        Facades\Collection::whereStructured()->each(fn ($collection) => $collection->structure()->trees()->each->tree());

        return Facades\Entry::all()->map(function (Entry $entry) {
            if (! $entry->published() || $entry->private() || $entry->isRedirect()) {
                return null;
            }

            return $entry->absoluteUrl();
        })->filter();
    }

    public function taxonomies(): Collection
    {
        $site = Site::current();

        try {
            return Facades\Taxonomy::all()
                ->filter(fn ($taxonomy) => view()->exists($taxonomy->template()))
                ->flatMap(function (Taxonomy $taxonomy) {
                    return $taxonomy->sites()->map(function ($site) use ($taxonomy) {
                        Site::setCurrent($site);

                        return $taxonomy->absoluteUrl();
                    });
                });
        } finally {
            Site::setCurrent($site->handle());
        }
    }

    public function terms(Collection $scopedTerms): Collection
    {
        return Facades\Term::all()
            ->merge($scopedTerms)
            ->filter(fn ($term) => view()->exists($term->template()))
            ->flatMap(function (LocalizedTerm $term) {
                return $term->taxonomy()->sites()->map(fn ($site) => $term->in($site)->absoluteUrl());
            });
    }

    public function scopedTerms(): Collection
    {
        return Facades\Collection::all()
            ->flatMap(fn (EntriesCollection $collection) => $this->collectionTerms($collection));
    }

    public function collectionTerms($collection): Collection
    {
        return $collection->taxonomies()
            ->flatMap(fn (Taxonomy $taxonomy) => $taxonomy->queryTerms()->get())
            ->map->collection($collection);
    }

    public function customRoutes(): Collection
    {
        $action = FrontendController::class.'@route';

        return collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn (Route $route) => $route->getActionName() === $action && ! Str::contains($route->uri(), '{'))
            ->map(fn (Route $route) => URL::tidy(Str::start($route->uri(), config('app.url').'/')));
    }
}
