<?php

namespace Statamic\Tags;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\GetsQuerySelectKeys;
use Statamic\Taxonomies\LocalizedTerm;

class Locales extends Tags
{
    use GetsQuerySelectKeys;

    /**
     * @var \Statamic\Contracts\Data\Content\Content
     */
    private $data;

    /**
     * The {{ locales }} tag.
     */
    public function index()
    {
        if (! $this->getData()) {
            return '';
        }

        $locales = $this->getLocales();

        if ($locales->isEmpty()) {
            return '';
        }

        return $locales;
    }

    /**
     * The {{ locales:count }} tag.
     */
    public function count()
    {
        return $this->getLocales()->count();
    }

    /**
     * The {{ locales:[key] }} tag.
     */
    public function wildcard($key)
    {
        if (! $site = Site::get($key)) {
            throw new \Exception("Site [$key] does not exist.");
        }

        if (! $data = $this->getLocalizedData($key)) {
            return '';
        }

        $data['locale'] = $this->getLocale($site);

        return $data;
    }

    /**
     * Get the locale collection to be used in the looping tag.
     *
     * @return Collection
     */
    private function getLocales()
    {
        return Site::all()->map(function ($locale, $key) {
            return $this->getLocale($key);
        })->pipe(function ($locales) {
            return $this->sort($locales);
        })->pipe(function ($locales) {
            return $this->addData($locales);
        })->filter(function ($item) {
            return $this->shouldInclude($item);
        })->values();
    }

    /**
     * Get a single locale array representation.
     *
     * @param  string  $key
     * @return array
     */
    private function getLocale($key)
    {
        $site = $key instanceof \Statamic\Sites\Site ? $key : Site::get($key);

        return array_merge($site->toAugmentedArray(), [
            'short' => $site->shortLocale(),
            'full' => $site->locale(),
            'key' => $site->handle(),
        ]);
    }

    /**
     * Add data to the locale collection.
     *
     * @param  Collection  $locales
     */
    private function addData($locales)
    {
        return $locales->map(function ($locale, $key) {
            $localized = $this->getLocalizedData($key);

            if ($localized || $this->params->bool('all')) {
                $localized = $this->fillWithNullsFromBlueprint($localized);
                $localized['locale'] = $locale;
                $localized['current'] = Site::current()->handle();
                $localized['is_current'] = $key === Site::current()->handle();
                $localized['exists'] = Arr::exists($localized, 'status');
                $localized['url'] = Arr::get($localized, 'url', $locale['url']);
                $localized['permalink'] = Arr::get($localized, 'permalink', $locale['permalink']);
            }

            return $localized;
        });
    }

    /**
     * Get the localized version of the data object as an array.
     *
     * @param  string  $locale
     * @return array
     */
    private function getLocalizedData($locale)
    {
        if (! $data = $this->getData()) {
            return null;
        }

        if (! $localized = $data->in($locale)) {
            return null;
        }

        if (method_exists($localized, 'published') && ! $localized->published()) {
            return null;
        }

        $keys = $this->getQuerySelectKeys($localized);

        return $localized->toAugmentedArray($keys);
    }

    /**
     * Get the data / content object.
     *
     * @return \Statamic\Contracts\Data\Content\Content
     */
    private function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $id = $this->params->get('id', $this->context->value('id'));

        $data = Data::find($id);

        $data = $this->workaroundForCollectionTaxonomyTerm($id, $data);

        return $this->data = $data;
    }

    private function workaroundForCollectionTaxonomyTerm($id, $data)
    {
        if (! $this->params->bool('collection_term_workaround', true)) {
            return $data;
        }

        if (! $data instanceof LocalizedTerm) {
            return $data;
        }

        // If the ID is the same as the root "page" item, then we'll just use that
        // term instead, as it'll have the collection associated with it already.
        if ($id === ($page = $this->context->get('page'))->id()) {
            return $page;
        }

        return $data;
    }

    /**
     * Sort the locale collection.
     *
     * @param  Collection  $locales
     * @return Collection
     */
    private function sort($locales)
    {
        if ($sort = $this->params->get('sort')) {
            [$sort, $dir] = $this->getSort($sort);
            $locales = ($dir === 'asc') ? $locales->sortBy($sort) : $locales->sortByDesc($sort);
        }

        if ($this->params->bool('current_first', true)) {
            $locales = $this->moveCurrentLocaleToFront($locales);
        }

        return $locales;
    }

    /**
     * Get the sort and direction values from a parameter string.
     *
     * @param  string  $sort
     * @return array
     */
    private function getSort($sort)
    {
        $dir = 'asc';

        if (Str::contains($sort, ':')) {
            [$sort, $dir] = explode(':', $sort);
            $dir = ($dir === 'desc') ? 'desc' : 'asc';
        }

        return [$sort, $dir];
    }

    /**
     * Move the current site locale to the front of the collection.
     *
     * @param  Collection  $locales
     * @return Collection
     */
    private function moveCurrentLocaleToFront($locales)
    {
        $key = Site::current()->handle();
        $current = $locales->pull($key);

        return collect([$key => $current])->merge($locales);
    }

    private function fillWithNullsFromBlueprint($item)
    {
        // If $item is already an array, it's an entry. We're done.
        if ($item) {
            return $item;
        }

        // Otherwise, the localization doesn't exist, but we don't want
        // the previous iteration of the loop to be carried over into
        // this iteration, so we'll add null values for all the fields.
        return $this->data->blueprint()
            ->fields()->all()
            ->map(function () {
                return null;
            })
            ->put('id', null)
            ->all();
    }

    private function shouldInclude($item)
    {
        if (! $item) {
            return false;
        }

        if (! $this->params->bool('self', true) && $item['locale']['handle']->value() === $this->data->locale()) {
            return false;
        }

        return true;
    }
}
