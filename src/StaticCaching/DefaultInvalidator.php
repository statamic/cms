<?php

namespace Statamic\StaticCaching;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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
            return $this->cacher->flush();
        }

        if ($item instanceof Entry) {
            $this->invalidateEntryUrls($item);
        } elseif ($item instanceof Term) {
            $this->invalidateTermUrls($item);
        } elseif ($item instanceof Nav) {
            $this->invalidateNavUrls($item);
        } elseif ($item instanceof Variables) {
            $this->invalidateGlobalUrls($item);
        } elseif ($item instanceof Collection) {
            $this->invalidateCollectionUrls($item);
        } elseif ($item instanceof Asset) {
            $this->invalidateAssetUrls($item);
        } elseif ($item instanceof Form) {
            $this->invalidateFormUrls($item);
        }
    }

    protected function invalidateFormUrls($form)
    {
        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "forms.{$form->handle()}.urls")
        );
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
        $entry->descendants()->merge([$entry])->each(function ($entry) {
            if (! $entry->isRedirect() && $url = $entry->absoluteUrl()) {
                $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            }
        });

        $this->cacher->invalidateUrls(
            collect(Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls"))->map(function (string $rule) use ($entry) {
                return ! isset(parse_url($rule)['scheme'])
                    ? Str::removeRight($entry->site()->url(), '/').Str::ensureLeft($rule, '/')
                    : $rule;
            })->values()->all()
        );
    }

    protected function invalidateTermUrls($term)
    {
        if ($url = $term->absoluteUrl()) {
            $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));

            $term->taxonomy()->collections()->each(function ($collection) use ($term) {
                if ($url = $term->collection($collection)->absoluteUrl()) {
                    $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
                }
            });
        }

        $this->cacher->invalidateUrls(
            collect(Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls"))->map(function (string $rule) use ($term) {
                return ! isset(parse_url($rule)['scheme'])
                    ? Str::removeRight($term->site()->url(), '/').Str::ensureLeft($rule, '/')
                    : $rule;
            })->values()->all()
        );
    }

    protected function invalidateNavUrls($nav)
    {
        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "navigation.{$nav->handle()}.urls")
        );
    }

    protected function invalidateGlobalUrls($variables)
    {
        $this->cacher->invalidateUrls(
            collect(Arr::get($this->rules, "globals.{$variables->globalSet()->handle()}.urls"))->map(function (string $rule) use ($variables) {
                return ! isset(parse_url($rule)['scheme'])
                    ? Str::removeRight($variables->site()->url(), '/').Str::ensureLeft($rule, '/')
                    : $rule;
            })->values()->all()
        );
    }

    protected function invalidateCollectionUrls($collection)
    {
        $collection->sites()->each(function (string $site) use (&$collection) {
            if ($url = $collection->absoluteUrl($site)) {
                $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            }

            $this->cacher->invalidateUrls(
                collect(Arr::get($this->rules, "collections.{$collection->handle()}.urls"))->map(function (string $rule) use ($site) {
                    return ! isset(parse_url($rule)['scheme'])
                        ? Str::removeRight(Site::get($site)->url(), '/').Str::ensureLeft($rule, '/')
                        : $rule;
                })->values()->all()
            );
        });
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return isset(parse_url($url)['scheme']);
    }

    private function splitUrlAndDomain(string $url): array
    {
        $parsed = parse_url($url);

        return [
            Arr::get($parsed, 'path', '/'),
            $parsed['scheme'].'://'.$parsed['host'],
        ];
    }
}
