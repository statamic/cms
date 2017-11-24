<?php

namespace Statamic\Data;

use Illuminate\Support\Collection;

class DataStore
{
    /**
     * @var Collection
     */
    private $data;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $default_locale;

    /**
     * DataStore constructor
     *
     * @param string $locale  Default locale
     */
    public function __construct($locale)
    {
        $this->locale = $this->default_locale = $locale;

        $this->data = collect([$locale => collect()]);
    }

    /**
     * Set the target locale
     *
     * @param $locale
     * @return $this
     */
    public function targetLocale($locale)
    {
        $this->locale = $locale;

        // Ensure a collection exists for the requested locale
        if (! $this->data->has($locale)) {
            $this->data->put($locale, collect());
        }

        return $this;
    }

    /**
     * Get a localized collection of data
     *
     * @param string|null $locale
     * @return \Illuminate\Support\Collection|\Statamic\Data\DataStore
     */
    public function locale($locale = null)
    {
        // Either get a specified locale, or the currently targeted locale.
        $locale = $locale ?: $this->locale;

        return $this->data->get($locale);
    }

    /**
     * Get a collection of data from the default locale
     *
     * @return Collection
     */
    private function defaultLocale()
    {
        return $this->data->get($this->default_locale);
    }

    /**
     * Resets the target locale
     */
    public function resetLocale()
    {
        $this->locale = $this->default_locale;
    }

    /**
     * Get the locales held in this store
     *
     * @return array
     */
    public function locales()
    {
        return $this->data->keys()->all();
    }

    /**
     * Remove a locale from the store
     *
     * @param string $locale
     */
    public function removeLocale($locale)
    {
        $this->data->forget($locale);
    }

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return Collection|null
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->locale();
        }

        $this->setData($data);
    }

    /**
     * Set the data
     *
     * @param array|null $data
     */
    private function setData($data)
    {
        $this->data->put($this->locale, collect($data));

        $this->resetLocale();
    }

    /**
     * Get a key from the data in a locale.
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->locale()->get($key, $default);
    }

    /**
     * Does the given key exist in the data?
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->locale()->has($key);
    }

    /**
     * Set a key in the data
     *
     * @param string $key   Key to set
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->locale()->put($key, $value);
    }

    /**
     * Remove a key from the data
     *
     * @param string $key Key to remove
     */
    public function remove($key)
    {
        $this->locale()->forget($key);
    }

    /**
     * Get all the data, merging with the default locale.
     *
     * @return array
     */
    public function all()
    {
        return $this->defaultLocale()->merge($this->locale())->all();
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data->toArray();
    }
}