<?php

namespace Statamic\StaticCaching;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Antlers;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Contracts\Structures\NavTree;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Structures\CollectionTree;
use Statamic\Taxonomies\LocalizedTerm;
use Illuminate\Support\Collection as IlluminateCollection;

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

        $urls = $this->getItemUrls($item);

        $this->cacher->invalidateUrls($urls);
    }

    public function refresh($item)
    {
        if (! config('statamic.static_caching.background_recache', false)) {
            $this->invalidate($item);

            return;
        }

        if ($this->rules === 'all') {
            $this->cacher->refreshUrls($this->cacher->getUrls()->all());

            return;
        }

        $urls = $this->getItemUrls($item);

        $this->cacher->refreshUrls($urls);
    }

    protected function getItemUrls($item)
    {
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

        return $urls;
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
        $rules = $this->parseInvalidationRules(
            Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls"),
            $entry->toAugmentedCollection()->merge(['parent_uri' => $entry->parent()?->uri()])->all()
        );

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
        $rules = $this->parseInvalidationRules(
            Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls"),
            $term->toAugmentedCollection()->all()
        );

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
        $rules = $this->parseInvalidationRules(
            Arr::get($this->rules, "navigation.{$nav->handle()}.urls"),
            $nav->toAugmentedCollection()->all()
        );

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
        $rules = $this->parseInvalidationRules(
            Arr::get($this->rules, "globals.{$variables->globalSet()->handle()}.urls"),
            $variables->toAugmentedCollection()->all()
        );

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

    private function parseInvalidationRules(array $rules, array $data): IlluminateCollection
    {
        return collect($rules)
            ->map(fn (string $rule) => $this->convertToAntlers($rule))
            ->map(fn (string $rule) => (string) Antlers::parse($rule, $data))
            ->filter();
    }

    private function convertToAntlers(string $route): string
    {
        if (Str::contains($route, '{{')) {
            return $route;
        }

        return preg_replace_callback('/{\s*([a-zA-Z0-9_\-]+)\s*}/', function ($match) {
            return "{{ {$match[1]} }}";
        }, $route);
    }
}
