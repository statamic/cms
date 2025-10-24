<?php

namespace Statamic\StaticCaching;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Structures\NavTree;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Structures\CollectionTree;
use Statamic\Support\Arr;
use Statamic\Taxonomies\LocalizedTerm;

class DefaultInvalidator implements Invalidator
{
    protected $cacher;
    protected $rules;

    public function __construct(Cacher $cacher, $rules = [])
    {
        $this->cacher = $cacher;
        $this->rules = $rules;
    }

    public function invalidate($item)
    {
        if ($this->rules === 'all') {
            $this->cacher->flush();

            return;
        }

        if ($item instanceof Entry) {
            $urls = $this->getEntryUrls($item);
        } elseif ($item instanceof LocalizedTerm) {
            $urls = $this->getTermUrls($item);
        } elseif ($item instanceof Nav) {
            $urls = $this->getNavUrls($item);
        } elseif ($item instanceof NavTree) {
            $urls = $this->getNavTreeUrls($item);
        } elseif ($item instanceof Variables) {
            $urls = $this->getGlobalUrls($item);
        } elseif ($item instanceof Collection) {
            $urls = $this->getCollectionUrls($item);
        } elseif ($item instanceof CollectionTree) {
            $urls = $this->getCollectionTreeUrls($item);
        } elseif ($item instanceof Asset) {
            $urls = $this->getAssetUrls($item);
        } elseif ($item instanceof Form) {
            $urls = $this->getFormUrls($item);
        } else {
            $urls = [];
        }

        collect($urls)
            ->filter(fn ($url) => is_array($url))
            ->each(fn ($url) => $this->cacher->invalidateUrl(...$url));

        $urls = collect($urls)->filter(fn ($url) => ! is_array($url));
        if ($urls->isNotEmpty()) {
            $this->cacher->invalidateUrls($urls->values()->all());
        }
    }

    public function invalidateAndRecache($item)
    {
        if (! config('statamic.static_caching.background_recache', false)) {
            return $this->invalidate($item);
        }

        if ($this->rules === 'all') {
            $this->recacheUrls($this->cacher->getUrls());

            return;
        }

        if ($item instanceof Entry) {
            $urls = $this->getEntryUrls($item);
        } elseif ($item instanceof LocalizedTerm) {
            $urls = $this->getTermUrls($item);
        } elseif ($item instanceof Nav) {
            $urls = $this->getNavUrls($item);
        } elseif ($item instanceof NavTree) {
            $urls = $this->getNavTreeUrls($item);
        } elseif ($item instanceof Variables) {
            $urls = $this->getGlobalUrls($item);
        } elseif ($item instanceof Collection) {
            $urls = $this->getCollectionUrls($item);
        } elseif ($item instanceof CollectionTree) {
            $urls = $this->getCollectionTreeUrls($item);
        } elseif ($item instanceof Asset) {
            $urls = $this->getAssetUrls($item);
        } elseif ($item instanceof Form) {
            $urls = $this->getFormUrls($item);
        } else {
            $urls = [];
        }

        $this->cacher->recacheUrls($urls);
    }

    protected function getFormUrls($form)
    {
        $rules = collect(Arr::get($this->rules, "forms.{$form->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = Site::all()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => URL::isAbsolute($rule))
                ->map(fn (string $rule) => URL::tidy($site->url().'/'.$rule, withTrailingSlash: false));
        })->flatten()->all();

        return [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getAssetUrls($asset)
    {
        $rules = collect(Arr::get($this->rules, "assets.{$asset->container()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = Site::all()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => URL::isAbsolute($rule))
                ->map(fn (string $rule) => URL::tidy($site->url().'/'.$rule, withTrailingSlash: false));
        })->flatten()->all();

        return [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getEntryUrls($entry)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls"));

        $urls = $entry->descendants()
            ->merge([$entry])
            ->reject(fn ($entry) => $entry->isRedirect())
            ->map->absoluteUrl()
            ->all();

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => URL::isAbsolute($rule))
            ->map(fn (string $rule) => URL::tidy($entry->site()->url().'/'.$rule, withTrailingSlash: false))
            ->all();

        return [
            ...$urls,
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getTermUrls($term)
    {
        $rules = collect(Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls"));

        if ($url = $term->absoluteUrl()) {
            $urls = $term->taxonomy()->collections()
                ->map(fn ($collection) => $term->collection($collection)->absoluteUrl())
                ->filter()
                ->prepend($url)
                ->all();
        }

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => URL::isAbsolute($rule))
            ->map(fn (string $rule) => URL::tidy($term->site()->url().'/'.$rule, withTrailingSlash: false))
            ->all();

        return [
            ...$urls ?? [],
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getNavUrls($nav)
    {
        $rules = collect(Arr::get($this->rules, "navigation.{$nav->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $nav->sites()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => URL::isAbsolute($rule))
                ->map(fn (string $rule) => URL::tidy(Site::get($site)->url().'/'.$rule, withTrailingSlash: false));
        })->flatten()->all();

        return [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getNavTreeUrls($tree)
    {
        $rules = collect(Arr::get($this->rules, "navigation.{$tree->structure()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => URL::isAbsolute($rule))
            ->map(fn (string $rule) => URL::tidy($tree->site()->url().'/'.$rule, withTrailingSlash: false))
            ->all();

        return [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getGlobalUrls($variables)
    {
        $rules = collect(Arr::get($this->rules, "globals.{$variables->globalSet()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => URL::isAbsolute($rule))
            ->map(fn (string $rule) => URL::tidy($variables->site()->url().'/'.$rule, withTrailingSlash: false))
            ->all();

        return [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getCollectionUrls($collection)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$collection->handle()}.urls"));

        $urls = $collection->sites()->map(fn ($site) => $collection->absoluteUrl($site))->filter()->all();

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $collection->sites()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => URL::isAbsolute($rule))
                ->map(fn (string $rule) => URL::tidy(Site::get($site)->url().'/'.$rule, withTrailingSlash: false));
        })->flatten()->all();

        return [
            ...$urls,
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }

    protected function getCollectionTreeUrls($tree)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$tree->collection()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => URL::isAbsolute($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => URL::isAbsolute($rule))
            ->map(fn (string $rule) => URL::tidy($tree->site()->url().'/'.$rule, withTrailingSlash: false))
            ->all();

        return [
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ];
    }
}
