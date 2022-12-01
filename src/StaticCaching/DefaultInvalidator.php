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
            Arr::get($this->rules, "forms.{$form->handle()}.urls")
        );
    }

    protected function invalidateAssetUrls($asset)
    {
        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "assets.{$asset->container()->handle()}.urls")
        );
    }

    protected function invalidateEntryUrls($entry)
    {
        $entry->descendants()->push($entry)->each(function ($entry) {
            if ($url = $entry->absoluteUrl()) {
                $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
            }
        });

        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "collections.{$entry->collectionHandle()}.urls")
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
            Arr::get($this->rules, "taxonomies.{$term->taxonomyHandle()}.urls")
        );
    }

    protected function invalidateNavUrls($nav)
    {
        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "navigation.{$nav->handle()}.urls")
        );
    }

    protected function invalidateGlobalUrls($set)
    {
        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "globals.{$set->handle()}.urls")
        );
    }

    protected function invalidateCollectionUrls($collection)
    {
        if ($url = $collection->absoluteUrl()) {
            $this->cacher->invalidateUrl(...$this->splitUrlAndDomain($url));
        }

        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "collections.{$collection->handle()}.urls")
        );
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
