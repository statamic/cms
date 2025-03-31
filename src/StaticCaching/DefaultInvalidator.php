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

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        Site::all()->each(function ($site) use ($rules) {
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->each(fn (string $rule) => $this->cacher->invalidateUrl(Str::removeRight($site->url(), '/').Str::ensureLeft($rule, '/')));
        });
    }

    protected function invalidateAssetUrls($asset)
    {
        $rules = collect(Arr::get($this->rules, "assets.{$asset->container()->handle()}.urls"));

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        Site::all()->each(function ($site) use ($rules) {
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->each(fn (string $rule) => $this->cacher->invalidateUrl(Str::removeRight($site->url(), '/').Str::ensureLeft($rule, '/')));
        });
    }

    protected function invalidateEntryUrls($entry)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls"));

        $entry->descendants()->merge([$entry])->each(function ($entry) {
            if (! $entry->isRedirect() && $url = $entry->absoluteUrl()) {
                $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            }
        });

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $this->cacher->invalidateUrls(
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($entry->site()->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
        );
    }

    protected function invalidateTermUrls($term)
    {
        $rules = collect(Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls"));

        if ($url = $term->absoluteUrl()) {
            $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));

            $term->taxonomy()->collections()->each(function ($collection) use ($term) {
                if ($url = $term->collection($collection)->absoluteUrl()) {
                    $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
                }
            });
        }

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $this->cacher->invalidateUrls(
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($term->site()->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
        );
    }

    protected function invalidateNavUrls($nav)
    {
        $rules = collect(Arr::get($this->rules, "navigation.{$nav->handle()}.urls"));

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $nav->sites()->each(function (string $site) use ($rules) {
            $this->cacher->invalidateUrls(
                $rules
                    ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                    ->map(fn (string $rule) => Str::removeRight(Site::get($site)->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
            );
        });
    }

    protected function invalidateNavTreeUrls($tree)
    {
        $rules = collect(Arr::get($this->rules, "navigation.{$tree->structure()->handle()}.urls"));

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $this->cacher->invalidateUrls(
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($tree->site()->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
        );
    }

    protected function invalidateGlobalUrls($variables)
    {
        $rules = collect(Arr::get($this->rules, "globals.{$variables->globalSet()->handle()}.urls"));

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $this->cacher->invalidateUrls(
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($variables->site()->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
        );
    }

    protected function invalidateCollectionUrls($collection)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$collection->handle()}.urls"));

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $collection->sites()->each(function (string $site) use (&$collection, $rules) {
            if ($url = $collection->absoluteUrl($site)) {
                $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            }

            $this->cacher->invalidateUrls(
                $rules
                    ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                    ->map(fn (string $rule) => Str::removeRight(Site::get($site)->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
            );
        });
    }

    protected function invalidateCollectionTreeUrls($tree)
    {
        $rules = collect(Arr::get($this->rules, "collections.{$tree->collection()->handle()}.urls"));

        $rules
            ->filter(fn (string $rule) => $this->isAbsoluteUrl($rule))
            ->each(fn (string $rule) => $this->cacher->invalidateUrl($rule));

        $this->cacher->invalidateUrls(
            $rules
                ->reject(fn (string $rule) => $this->isAbsoluteUrl($rule))
                ->map(fn (string $rule) => Str::removeRight($tree->site()->url(), '/').Str::ensureLeft($rule, '/'))->values()->all()
        );
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
