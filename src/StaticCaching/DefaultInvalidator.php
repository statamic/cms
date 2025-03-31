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
use Statamic\Structures\CollectionTree;
use Statamic\Support\Arr;
use Statamic\Support\Str;
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
            $this->invalidateEntryUrls($item);
        } elseif ($item instanceof LocalizedTerm) {
            $this->invalidateTermUrls($item);
        } elseif ($item instanceof Nav) {
            $this->invalidateNavUrls($item);
        } elseif ($item instanceof NavTree) {
            $this->invalidateNavTreeUrls($item);
        } elseif ($item instanceof Variables) {
            $this->invalidateGlobalUrls($item);
        } elseif ($item instanceof Collection) {
            $this->invalidateCollectionUrls($item);
        } elseif ($item instanceof CollectionTree) {
            $this->invalidateCollectionTreeUrls($item);
        } elseif ($item instanceof Asset) {
            $this->invalidateAssetUrls($item);
        } elseif ($item instanceof Form) {
            $this->invalidateFormUrls($item);
        }
    }

    protected function invalidateFormUrls($form)
    {
        $rules = collect(Arr::get($this->rules, "forms.{$form->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = Site::all()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($site->url(), '/').Str::ensureLeft($rule, '/'));
        })->flatten()->all();

        $this->cacher->invalidateUrls([
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateAssetUrls($asset)
    {
        $rules = collect(Arr::get($this->rules, "assets.{$asset->container()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = Site::all()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($site->url(), '/').Str::ensureLeft($rule, '/'));
        })->flatten()->all();

        $this->cacher->invalidateUrls([
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateEntryUrls($entry)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls"));

        $urls = $entry->descendants()
            ->merge([$entry])
            ->reject(fn ($entry) => $entry->isRedirect())
            ->map->absoluteUrl()
            ->all();

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->map(fn (string $rule) => Str::removeRight($entry->site()->url(), '/').Str::ensureLeft($rule, '/'))
            ->all();

        $this->cacher->invalidateUrls([
            ...$urls,
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateTermUrls($term)
    {
        $rules = collect(Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls"));

        if ($url = $term->absoluteUrl()) {
            $urls = $term->taxonomy()->collections()
                ->map(fn ($collection) => $term->collection($collection)->absoluteUrl())
                ->filter()
                ->prepend($url)
                ->all();
        }

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->map(fn (string $rule) => Str::removeRight($term->site()->url(), '/').Str::ensureLeft($rule, '/'))
            ->all();

        $this->cacher->invalidateUrls([
            ...$urls ?? [],
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateNavUrls($nav)
    {
        $rules = collect(Arr::get($this->rules, "navigation.{$nav->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $nav->sites()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight(Site::get($site)->url(), '/').Str::ensureLeft($rule, '/'));
        })->flatten()->all();

        $this->cacher->invalidateUrls([
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateNavTreeUrls($tree)
    {
        $rules = collect(Arr::get($this->rules, "navigation.{$tree->structure()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->map(fn (string $rule) => Str::removeRight($tree->site()->url(), '/').Str::ensureLeft($rule, '/'))
            ->all();

        $this->cacher->invalidateUrls([
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateGlobalUrls($variables)
    {
        $rules = collect(Arr::get($this->rules, "globals.{$variables->globalSet()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->map(fn (string $rule) => Str::removeRight($variables->site()->url(), '/').Str::ensureLeft($rule, '/'))
            ->all();

        $this->cacher->invalidateUrls([
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateCollectionUrls($collection)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$collection->handle()}.urls"));

        $urls = $collection->sites()->map(fn ($site) => $collection->absoluteUrl($site))->filter()->all();

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $collection->sites()->map(function ($site) use ($rules) {
            return $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight(Site::get($site)->url(), '/').Str::ensureLeft($rule, '/'));
        })->flatten()->all();

        $this->cacher->invalidateUrls([
            ...$urls,
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    protected function invalidateCollectionTreeUrls($tree)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$tree->collection()->handle()}.urls"));

        $absoluteUrls = $rules->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))->all();

        $prefixedRelativeUrls = $rules
            ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->map(fn (string $rule) => Str::removeRight($tree->site()->url(), '/').Str::ensureLeft($rule, '/'))
            ->all();

        $this->cacher->invalidateUrls([
            ...$absoluteUrls,
            ...$prefixedRelativeUrls,
        ]);
    }

    private function isAbsoluteUrl(string $url)
    {
        return isset(parse_url($url)['scheme']);
    }

    private function splitUrlAndDomain(string $url)
    {
        $parsed = parse_url($url);

        return [
            Arr::get($parsed, 'path', '/'),
            $parsed['scheme'].'://'.$parsed['host'],
        ];
    }
}
