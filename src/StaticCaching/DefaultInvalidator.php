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
        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "forms.{$form->handle()}.urls")
        );

        $this->cacher->warmUrls($rules);
    }

    protected function invalidateAssetUrls($asset)
    {
        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "assets.{$asset->container()->handle()}.urls")
        );

        $this->cacher->warmUrls($rules);
    }

    protected function invalidateEntryUrls($entry)
    {
        $entry->descendants()->push($entry)->each(function ($entry) {
            if (! $entry->isRedirect() && $url = $entry->absoluteUrl()) {
                $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
                $this->cacher->warmUrl($url);
            }
        });

        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls")
        );

        $this->cacher->warmUrls($rules);
    }

    protected function invalidateTermUrls($term)
    {
        if ($url = $term->absoluteUrl()) {
            $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            $this->cacher->warmUrl($term->absoluteUrl());

            $term->taxonomy()->collections()->each(function ($collection) use ($term) {
                if ($url = $term->collection($collection)->absoluteUrl()) {
                    $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
                    $this->cacher->warmUrl($url);
                }
            });
        }

        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls")
        );

        $this->cacher->warmUrls($rules);
    }

    protected function invalidateNavUrls($nav)
    {
        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "navigation.{$nav->handle()}.urls")
        );

        $this->cacher->warmUrls($rules);
    }

    protected function invalidateGlobalUrls($set)
    {
        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "globals.{$set->handle()}.urls")
        );

        $this->cacher->warmUrls($rules);
    }

    protected function invalidateCollectionUrls($collection)
    {
        if ($url = $collection->absoluteUrl()) {
            $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            $this->cacher->warmUrl($url);
        }

        $this->cacher->invalidateUrls(
            $rules = Arr::get($this->rules, "collections.{$collection->handle()}.urls")
        );

        $this->cacher->warmUrls($rules);
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
