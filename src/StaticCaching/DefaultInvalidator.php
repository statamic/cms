<?php

namespace Statamic\StaticCaching;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Support\Arr;

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
        } elseif ($item instanceof GlobalSet) {
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
        $this->cacher->invalidateUrls($this->getFormUrls($form));
    }

    protected function invalidateAssetUrls($asset)
    {
        $this->cacher->invalidateUrls($this->getAssetUrls($asset));
    }

    protected function invalidateEntryUrls($entry)
    {
        $this->cacher->invalidateUrls($this->getEntryUrls($entry));
    }

    protected function invalidateTermUrls($term)
    {
        $this->cacher->invalidateUrls($this->getTermUrls($term));
    }

    protected function invalidateNavUrls($nav)
    {
        $this->cacher->invalidateUrls($this->getNavUrls($nav));
    }

    protected function invalidateGlobalUrls($set)
    {
        $this->cacher->invalidateUrls($this->getGlobalUrls($set));
    }

    protected function invalidateCollectionUrls($collection)
    {
        $this->cacher->invalidateUrls($this->getCollectionUrls($collection));
    }

    public function invalidateAndRecache($item)
    {
        $urls = [];

        if ($this->rules === 'all') {
            $this->recacheUrls($this->cacher->getUrls());

            return;
        }

        if ($item instanceof Entry) {
            $urls = $this->getEntryUrls($item);
        } elseif ($item instanceof Term) {
            $urls = $this->getTermUrls($item);
        } elseif ($item instanceof Nav) {
            $urls = $this->getNavUrls($item);
        } elseif ($item instanceof GlobalSet) {
            $this->getGlobalUrls($item);
        } elseif ($item instanceof Collection) {
            $urls = $this->getCollectionUrls($item);
        } elseif ($item instanceof Asset) {
            $urls = $this->getAssetUrls($item);
        } elseif ($item instanceof Form) {
            $urls = $this->getFormUrls($item);
        }

        $this->recacheUrls($urls);
    }

    public function recacheUrls($urls)
    {
        collect($urls)->each(fn ($url) => is_array($url) ? $this->recacheUrl(...$url) : $this->recacheUrl($url));
    }

    public function recacheUrl($path, $domain = null)
    {
        StaticRecacheJob::dispatch($path, $domain);
    }

    private function getFormUrls($form)
    {
        return Arr::get($this->rules, "forms.{$form->handle()}.urls");
    }

    protected function getAssetUrls($asset)
    {
        return Arr::get($this->rules, "assets.{$asset->container()->handle()}.urls");
    }

    protected function getEntryUrls($entry)
    {
        $urls = $entry->descendants()->merge([$entry])->map(function ($entry) {
            if (! $entry->isRedirect() && $url = $entry->absoluteUrl()) {
                return $this->splitUrlAndDomain($url);
            }
        })->filter();

        return $urls->merge(Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls"))->all();
    }

    protected function getTermUrls($term)
    {
        $urls = collect();
        if ($url = $term->absoluteUrl()) {
            $urls = $urls->push($this->splitUrlAndDomain($url));

            $urls = $urls->merge($term->taxonomy()->collections()->each(function ($collection) use ($term) {
                if ($url = $term->collection($collection)->absoluteUrl()) {
                    return $this->splitUrlAndDomain($url);
                }
            })->filter();
        }

        return $urls->merge(Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls"))->all();
    }

    protected function getNavUrls($nav)
    {
        return Arr::get($this->rules, "navigation.{$nav->handle()}.urls");
    }

    protected function getGlobalUrls($set)
    {
        return Arr::get($this->rules, "globals.{$set->handle()}.urls");
    }

    protected function getCollectionUrls($collection)
    {
        $urls = [];
        if ($url = $collection->absoluteUrl()) {
            $urls[] = $this->splitUrlAndDomain($url);
        }

        return array_merge($urls, Arr::get($this->rules, "collections.{$collection->handle()}.urls"));
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
