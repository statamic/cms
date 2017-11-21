<?php

namespace Statamic\Contracts\Data;

use Statamic\Contracts\CP\Editable;
use Statamic\Contracts\HasFieldset;
use Illuminate\Contracts\Support\Arrayable;

interface Data extends Arrayable, Editable, HasFieldset
{
    /**
     * Get or set the current locale
     *
     * @param string|null $locale
     */
    public function locale($locale = null);

    /**
     * Reset the locale back to the default
     *
     * @return void
     */
    public function resetLocale();

    /**
     * Get the object in a specific locale
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\LocalizedData
     */
    public function in($locale);

    /**
     * Get the locales this data exists in.
     *
     * @return array
     */
    public function locales();

    /**
     * Is this data localized into the requested locale?
     *
     * @param string $locale
     * @return bool
     */
    public function hasLocale($locale);

    /**
     * Remove data for a locale.
     *
     * @param string $locale
     */
    public function removeLocale($locale);

    /**
     * Is the object set to the default locale?
     *
     * @return bool
     */
    public function isDefaultLocale();

    /**
     * Get or set all the data for the current locale
     *
     * @param array|null $data
     * @return $this|array
     */
    public function data($data = null);

    /**
     * Get or set the data for a locale
     *
     * @param string $locale
     * @param array|null   $data
     * @return $this|array
     */
    public function dataForLocale($locale, $data = null);

    /**
     * Get all the data for this locale, merged with the default locale data
     *
     * @return array
     */
    public function dataWithDefaultLocale();

    /**
     * Get data from the default locale
     *
     * @return array
     */
    public function defaultData();

    /**
     * Get a key from the data, without falling back to the cascade.
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Get a key from the data, and fall back to the default locale
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function getWithDefaultLocale($key, $default = null);

    /**
     * Get a key from the data, and fall back to cascade (folder.yaml + default locale)
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function getWithCascade($key, $default = null);

    /**
     * Does the given key exist in the data?
     *
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Does the given key exist in the data, including the default locale?
     *
     * @param string $key
     * @return bool
     */
    public function hasWithDefaultLocale($key);

    /**
     * Does the given key exist in the data, including the cascade?
     *
     * @param string $key
     * @return bool
     */
    public function hasWithCascade($key);

    /**
     * Set a key in the data
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * Remove a key from the data
     *
     * @param string $key Key to remove
     * @return $this
     */
    public function remove($key);

    /**
     * Get the data, processed by its fieldtypes
     *
     * @return array
     */
    public function processedData();

    /**
     * Sync the original object with the current object
     *
     * @return $this
     */
    public function syncOriginal();

    /**
     * Get or set the data type (extension)
     *
     * @param string|null $type
     * @return $this|string
     */
    public function dataType($type = null);

    /**
     * Get or set the content
     *
     * @param string|null $content
     * @return $this|string
     */
    public function content($content = null);

    /**
     * Parses the content as their content type, smartypants, and as a template
     *
     * @return mixed|string
     */
    public function parseContent();

    /**
     * Get or set the path
     *
     * @param string|null $path
     * @return string
     */
    public function path($path = null);

    /**
     * Get the path to a localized version
     *
     * @param string $locale
     * @return string
     */
    public function localizedPath($locale);

    /**
     * Get the path before the object was modified.
     *
     * @return string
     */
    public function originalPath();

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    public function originalLocalizedPath($locale);

    /**
     * Get or set the ID
     *
     * @param mixed $id
     * @return mixed
     * @throws \Statamic\Exceptions\UuidExistsException
     */
    public function id($id = null);

    /**
     * Ensure there is an ID
     *
     * @param bool $save  Whether the file get saved once a UUID is generated
     * @return static
     */
    public function ensureId($save = false);

    /**
     * Convert this to an array (for use in templates)
     *
     * @return array
     */
    public function toArray();

    /**
     * Get the supplemented data
     *
     * @return array
     */
    public function supplements();

    /**
     * Get a key in the supplemental data
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback data
     * @return mixed
     */
    public function getSupplement($key, $default = null);

    /**
     * Set a key in the supplemental data
     *
     * @param string $key   Key to set
     * @param mixed  $value Value to set
     * @return $this|mixed
     */
    public function setSupplement($key, $value);

    /**
     * Remove a key from the supplemental data
     *
     * @param string $key Key to remove
     * @return $this|mixed
     */
    public function removeSupplement($key);

    /**
     * Save the data
     *
     * @return mixed
     */
    public function save();

    /**
     * Delete the data
     *
     * @return mixed
     */
    public function delete();
}
