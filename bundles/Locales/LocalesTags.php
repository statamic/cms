<?php

namespace Statamic\Addons\Locales;

use Statamic\API\Str;
use Statamic\API\Config;
use Statamic\API\Content;
use Statamic\Extend\Tags;
use Illuminate\Support\Collection;

class LocalesTags extends Tags
{
    /**
     * @var \Statamic\Contracts\Data\Content\Content
     */
    private $data;

    /**
     * The {{ locales }} tag.
     *
     * @return string
     */
    public function index()
    {
        return $this->parseLoop($this->getLocales());
    }

    /**
     * The {{ locale:[key] }} tag.
     *
     * @param string $method  The locale key
     * @param array  $args
     * @return string
     */
    public function __call($method, $args)
    {
        $data = $this->getLocalizedData($key = $this->tag_method);

        $data['locale'] = $this->getLocale($key);

        return $this->parse($data);
    }

    /**
     * Get the locale collection to be used in the looping tag.
     *
     * @return Collection
     */
    private function getLocales()
    {
        return collect(Config::get('system.locales'))->map(function ($locale, $key) {
            return $this->getLocale($key);
        })->pipe(function ($locales) {
            return $this->sort($locales);
        })->pipe(function ($locales) {
            return $this->addData($locales);
        });
    }

    /**
     * Get a single locale array representation.
     *
     * @param  string $key
     * @return array
     */
    private function getLocale($key)
    {
        $locale = Config::get("system.locales.{$key}");
        $locale['key'] = $key;
        return $locale;
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
            $localized['current'] = site_locale();
            $localized['is_current'] = $key === site_locale();
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
        return $this->getData()->in($locale)->toArray();
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

        $id = $this->get('id', array_get($this->context, 'id'));

        return $this->data = Content::find($id);
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
        $key = site_locale();
        $current = $locales->pull($key);
        return collect([$key => $current])->merge($locales);
    }
}
