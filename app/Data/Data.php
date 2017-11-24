<?php

namespace Statamic\Data;

use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Parse;
use Statamic\API\Str;
use Statamic\API\Term;
use Statamic\API\Taxonomy;
use Statamic\Exceptions\UuidExistsException;
use Statamic\Exceptions\UrlNotFoundException;
use Statamic\Contracts\Data\Data as DataContract;

abstract class Data implements DataContract
{
    /**
     * The target/active locale
     *
     * @var string
     */
    private $locale;

    /**
     * The object's data, available in multiple locales.
     *
     * @var DataStore
     */
    private $store;

    /**
     * Attributes that make up the object
     *
     * @var array
     */
    protected $attributes;

    /**
     * A representation of the original version of this object used for comparisons when saving.
     *
     * @var array
     */
    protected $original;

    /**
     * Supplemental attributes used when converting to arrays.
     *
     * @var array
     */
    protected $supplements = [];

    /**
     * Whether taxonomies should be supplemented
     *
     * @var bool
     */
    protected $supplement_taxonomies;

    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->store = new DataStore($this->locale());
    }

    /**
     * Get or set the current locale
     *
     * @param string|null $locale
     */
    public function locale($locale = null)
    {
        if (is_null($locale)) {
            return $this->locale ?: default_locale();
        }

        $this->locale = $locale;
    }

    /**
     * Reset the locale back to the default
     *
     * @return void
     */
    public function resetLocale()
    {
        $this->locale = default_locale();
    }

    /**
     * Get the object in a specific locale
     *
     * @param string|null $locale
     * @return LocalizedData
     */
    public function in($locale)
    {
        return new LocalizedData($locale, $this);
    }

    /**
     * Get the locales this data exists in.
     *
     * @return array
     */
    public function locales()
    {
        return $this->store->locales();
    }

    /**
     * Is this data localized into the requested locale?
     *
     * @param string $locale
     * @return bool
     */
    public function hasLocale($locale)
    {
        return in_array($locale, $this->locales());
    }

    /**
     * Remove data for a locale.
     *
     * @param string $locale
     */
    public function removeLocale($locale)
    {
        $this->store->removeLocale($locale);
    }

    /**
     * Is the object set to the default locale?
     *
     * @return bool
     */
    public function isDefaultLocale()
    {
        // If the locale hasn't been explicitly modified, then it's the default.
        if (! $this->locale) {
            return true;
        }

        return $this->locale() === default_locale();
    }

    /**
     * Get or set all the data for the current locale
     *
     * @param array|null $data
     * @return $this|array
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->getData();
        }

        return $this->setData($data);
    }

    /**
     * Get all the data for the current locale
     *
     * @return array
     */
    private function getData()
    {
        return $this->datastore(function ($store) {
            return $store->data()->all();
        });
    }

    /**
     * Set all the data for the current locale
     *
     * @param array $data
     * @return $this
     */
    private function setData($data)
    {
        $this->datastore(function ($store) use ($data){
            $store->data($data);
        });

        return $this;
    }

    /**
     * Get or set the data for a locale
     *
     * @param string $locale
     * @param array|null   $data
     * @return $this|array
     */
    public function dataForLocale($locale, $data = null)
    {
        if (is_null($data)) {
            return $this->getDataForLocale($locale);
        }

        return $this->setDataForLocale($locale, $data);
    }

    /**
     * Get the data for a specific locale
     *
     * @param string $locale
     * @return array|$this
     */
    protected function getDataForLocale($locale)
    {
        return $this->datastore(function ($store) use ($locale) {
            $store->targetLocale($locale);
            return $store->data()->all();
        });
    }

    /**
     * Set all the data for a specific locale
     *
     * @param string $locale
     * @param array  $data
     * @return $this
     */
    protected function setDataForLocale($locale, $data)
    {
        $this->datastore(function ($store) use ($locale, $data) {
            $store->targetLocale($locale);
            $store->data($data);
        });

        return $this;
    }

    /**
     * Get all the data for this locale, merged with the default locale data
     *
     * @return array
     */
    public function dataWithDefaultLocale()
    {
        return array_merge($this->defaultData(), $this->data());
    }

    /**
     * Get data from the default locale
     *
     * @return array
     */
    public function defaultData()
    {
        return $this->datastore(function ($store) {
            $store->resetLocale();
            return $store->data()->all();
        });
    }

    /**
     * Get a key from the data, without falling back to the cascade.
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->datastore(function ($store) use ($key, $default) {
            return $store->get($key, $default);
        });
    }

    /**
     * Get a key from the data, and fall back to the default locale
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function getWithDefaultLocale($key, $default = null)
    {
        return Helper::pick(
            $this->get($key),
            array_get($this->defaultData(), $key),
            $default
        );
    }

    /**
     * Get a key from the data, and fall back to cascade (folder.yaml + default locale)
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function getWithCascade($key, $default = null)
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $cascade = array_merge($this->cascadingData(), $this->defaultData());

        return Helper::pick(
            array_get($cascade, $key),
            $default
        );
    }

    /**
     * Does the given key exist in the data?
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->datastore(function ($store) use ($key) {
            return $store->has($key);
        });
    }

    /**
     * Does the given key exist in the data, including the default locale?
     *
     * @param string $key
     * @return bool
     */
    public function hasWithDefaultLocale($key)
    {
        return $this->getWithDefaultLocale($key) !== null;
    }

    /**
     * Does the given key exist in the data, including the cascade?
     *
     * @param string $key
     * @return bool
     */
    public function hasWithCascade($key)
    {
        return $this->getWithCascade($key) !== null;
    }

    /**
     * Set a key in the data
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->datastore(function ($store) use ($key, $value) {
            $store->set($key, $value);
        });

        return $this;
    }

    /**
     * Remove a key from the data
     *
     * @param string $key Key to remove
     * @return $this
     */
    public function remove($key)
    {
        $this->datastore(function ($store) use ($key) {
            $store->remove($key);
        });

        return $this;
    }

    /**
     * Access the data store while targeting the current locale
     *
     * @param $callback
     */
    protected function datastore($callback)
    {
        $this->store->targetLocale($this->locale());

        $return = $callback($this->store);

        $this->store->resetLocale();

        return $return;
    }

    /**
     * Get data from the cascade (folder.yaml files)
     *
     * @return array
     */
    protected function cascadingData()
    {
        return [];
    }

    /**
     * Get the data, processed by its fieldtypes
     *
     * @return array
     */
    public function processedData()
    {
        $data = $this->dataWithDefaultLocale();

        $fieldtypes = collect($this->fieldset()->fieldtypes())->keyBy(function($fieldtype) {
            return $fieldtype->getFieldConfig('name');
        });

        foreach ($data as $field_name => $field_data) {
            if ($fieldtype = $fieldtypes->get($field_name)) {
                $data[$field_name] = $fieldtype->preProcess($field_data);
            }
        }

        return $data;
    }

    /**
     * Sync the original object with the current object
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->toOriginal();

        return $this;
    }

    /**
     * Get this object's original state as an array
     *
     * @return array
     */
    protected function toOriginal()
    {
        return [
            'data' => $this->store->toArray(),
            'attributes' => $this->attributes
        ];
    }

    /**
     * Get or set the data type (extension)
     *
     * @param string|null $type
     * @return $this|string
     */
    public function dataType($type = null)
    {
        if (is_null($type)) {
            return array_get($this->attributes, 'data_type');
        }

        $this->attributes['data_type'] = $type;

        return $this;
    }

    /**
     * Get or set the content
     *
     * @param string|null $content
     * @return $this|string
     */
    public function content($content = null)
    {
        if (is_null($content)) {
            return $this->getWithCascade('content');
        }

        $this->set('content', $content);

        return $this;
    }

    /**
     * Parses the content as their content type, and smartypants
     *
     * @return mixed|string
     */
    public function parseContent()
    {
        if (! $content = $this->content()) {
            return;
        }

        switch ($this->dataType()) {
            case 'markdown':
            case 'md':
                $content = markdown($content);
                break;

            case 'text':
            case 'txt':
                $content = nl2br(strip_tags($content));
                break;

            case 'textile':
                $content = textile($content);
        }

        if (Config::get('theming.smartypants')) {
            $content = smartypants($content);
        }

        if (! $this->getWithCascade('parse_content', true)) {
            $content = Str::replace($content, '{', '&lbrace;');
            $content = Str::replace($content, '}', '&rbrace;');
        }

        try {
            return Parse::template(
                $content,
                array_merge($this->store->all(), datastore()->getAll())
            );
        } catch (UrlNotFoundException $e) {
            // If content has a {{ 404 }} in it, it'll actually throw the 404.
            // We don't want to perform the redirect, so we'll just return nothing.
            return;
        }
    }

    /**
     * Get or set the path
     *
     * @param string|null $path
     * @return string
     */
    abstract public function path($path = null);

    /**
     * Get the path to a localized version
     *
     * @param string $locale
     * @return string
     */
    abstract public function localizedPath($locale);

    /**
     * Get the path before the object was modified.
     *
     * @return string
     */
    abstract public function originalPath();

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    abstract public function originalLocalizedPath($locale);

    /**
     * Get or set the ID
     *
     * @param mixed $id
     * @return mixed
     * @throws \Statamic\Exceptions\UuidExistsException
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            // The ID comes from the default locale.
            return $this->datastore(function ($store) {
                $store->targetLocale(default_locale());
                return $store->get('id');
            });
        }

        if ($this->id()) {
            throw new UuidExistsException('Data already has an ID');
        }

        // If true is passed in, we'll generate a UUID. Otherwise just use what was passed.
        $id = ($id === true) ? Helper::makeUuid() : $id;
        $this->set('id', $id);

        return $this;
    }

    /**
     * Ensure there is an ID
     *
     * @param bool $save Whether the file get saved once a UUID is generated
     * @return $this
     */
    public function ensureId($save = false)
    {
        try {
            $this->id(true);

            if ($save) {
                $this->save(false);
            }
        } catch (UuidExistsException $e) {
            // It's already has a UUID, do nothing.
        }

        return $this;
    }

    /**
     * Convert this to an array (for use in templates)
     *
     * @return array
     */
    public function toArray()
    {
        $this->supplement();

        $content_raw = $this->content();
        $content = $this->parseContent();

        $array = array_merge(
            $this->cascadingData(),
            $this->dataWithDefaultLocale(),
            $this->supplements,
            compact('content', 'content_raw')
        );

        $this->supplements = [];

        return $array;
    }

    /**
     * Get the supplemented data
     *
     * @return array
     */
    public function supplements()
    {
        return $this->supplements;
    }

    /**
     * Get a key in the supplemental data
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback data
     * @return mixed
     */
    public function getSupplement($key, $default = null)
    {
        return array_get($this->supplements, $key, $default);
    }

    /**
     * Set a key in the supplemental data
     *
     * @param string $key   Key to set
     * @param mixed  $value Value to set
     * @return $this|mixed
     */
    public function setSupplement($key, $value)
    {
        $this->supplements[$key] = $value;

        return $this;
    }

    /**
     * Remove a key from the supplemental data
     *
     * @param string $key Key to remove
     * @return $this|mixed
     */
    public function removeSupplement($key)
    {
        unset($this->supplements[$key]);

        return $this;
    }

    /**
     * Get a representation of the object in its simplest form suitable for serialization
     *
     * @return array
     */
    public function shrinkWrap()
    {
        return [
            'attributes' => $this->attributes,
            'data' => $this->store->toArray()
        ];
    }

    /**
     * Save the data
     *
     * @return mixed
     */
    abstract public function save();

    /**
     * Delete the data
     *
     * @return mixed
     */
    abstract public function delete();

    /**
     * Whether the data can be taxonomized
     *
     * @return bool
     */
    abstract public function isTaxonomizable();

    /**
     * Enable taxonomies to be added when supplementing occurs
     *
     * @return void
     */
    public function supplementTaxonomies()
    {
        $this->supplement_taxonomies = true;
    }

    /**
     * Supplement the data with taxonomies
     *
     * @return void
     */
    protected function addTaxonomySupplements()
    {
        Taxonomy::all()->each(function ($taxonomy, $taxonomy_handle) {
            if (! $this->hasWithDefaultLocale($taxonomy_handle)) {
                return;
            }

            $terms = $this->getWithDefaultLocale($taxonomy_handle);

            $this->supplements[$taxonomy_handle.'_raw'] = $terms;

            // Do nothing if there's a blank field.
            if ($terms == '') {
                return;
            }

            $terms = collect($terms)->map(function ($term) use ($taxonomy_handle) {
                return Term::whereSlug(Term::normalizeSlug($term), $taxonomy_handle);
            });

            $this->supplements[$taxonomy_handle] = $terms->all();
        });
    }
}
