<?php

namespace Statamic\Tags;

use Statamic\Support\Str;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Tags\Tags;
use Statamic\Facades\Config;
use Illuminate\Support\Collection;

class Locales extends Tags
{
    /**
     * @var \Statamic\Contracts\Data\Content\Content
     */
    private $data;

    /**
     * The {{ locales }} tag.
     */
    public function index()
    {
        return $this->getLocales();
    }

    /**
     * The {{ locale:[key] }} tag.
     */
    public function wildcard($key)
    {
        if (! $site = Site::get($key)) {
            throw new \Exception("Site [$key] does not exist.");
        }

        $data = $this->getLocalizedData($key);

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
        })->values();
    }

    /**
     * Get a single locale array representation.
     *
     * @param  string $key
     * @return array
     */
    private function getLocale($key)
    {
        $site = $key instanceof \Statamic\Sites\Site ? $key : Site::get($key);

        return [
            'key' => $site->handle(),
            'handle' => $site->handle(),
            'name' => $site->name(),
            'full' => $site->locale(),
            'short' => $site->shortLocale(),
        ];
    }

    /**
     * Add data to the locale collection.
     *
     * @param Collection $locales
     */
    private function addData($locales)
    {
        return $locales->map(function ($locale, $key) {
            $localized = $this->getLocalizedData($key);
            $localized['locale'] = $locale;
            $localized['current'] = Site::current()->handle();
            $localized['is_current'] = $key === Site::current()->handle();
            return $localized;
        });
    }

    /**
     * Get the localized version of the data object as an array.
     *
     * @param  string $locale
     * @return array
     */
    private function getLocalizedData($locale)
    {
        return $this->getData()->in($locale)->toAugmentedArray();
    }

    /**
     * Get the data / content object
     *
     * @return \Statamic\Contracts\Data\Content\Content
     */
    private function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $id = $this->get('id', $this->context->get('id'));

        return $this->data = Entry::find($id);
    }

    /**
     * Sort the locale collection.
     *
     * @param  Collection $locales
     * @return Collection
     */
    private function sort($locales)
    {
        if ($sort = $this->get('sort')) {
            list($sort, $dir) = $this->getSort($sort);
            $locales = ($dir === 'asc') ? $locales->sortBy($sort) : $locales->sortByDesc($sort);
        }

        if ($this->getBool('current_first', true)) {
            $locales = $this->moveCurrentLocaleToFront($locales);
        }

        return $locales;
    }

    /**
     * Get the sort and direction values from a parameter string.
     *
     * @param  string $sort
     * @return array
     */
    private function getSort($sort)
    {
        $dir = 'asc';

        if (Str::contains($sort, ':')) {
            list($sort, $dir) = explode(':', $sort);
            $dir = ($dir === 'desc') ? 'desc' : 'asc';
        }

        return [$sort, $dir];
    }

    /**
     * Move the current site locale to the front of the collection.
     *
     * @param  Collection $locales
     * @return Collection
     */
    private function moveCurrentLocaleToFront($locales)
    {
        $key = Site::current()->handle();
        $current = $locales->pull($key);
        return collect([$key => $current])->merge($locales);
    }
}
