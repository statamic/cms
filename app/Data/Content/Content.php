<?php

namespace Statamic\Data\Content;

use Statamic\API\Config;
use Statamic\API\File;
use Statamic\API\Helper;
use Statamic\API\URL;
use Statamic\API\YAML;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Data\Data;
use League\Flysystem\FileNotFoundException;
use Statamic\Exceptions\UuidExistsException;
use Statamic\Contracts\Data\Content\Content as ContentContract;

abstract class Content extends Data implements ContentContract
{
    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    abstract public function slug($slug = null);

    /**
     * Get or set the order key
     *
     * @param mixed|null $order
     * @return mixed
     */
    public function order($order = null)
    {
        if (is_null($order)) {
            return array_get($this->attributes, 'order');
        }

        $this->attributes['order'] = $order;

        return $this;
    }

    /**
     * Get or set the publish status
     *
     * @param null|bool $published
     * @return void|bool
     */
    public function published($published = null)
    {
        if (is_null($published)) {
            // The publish state may be stored within the front-matter.
            // Otherwise, we'll get it from the attributes.
            $setStatus = $this->get('published');
            return is_null($setStatus) ? array_get($this->attributes, 'published', true) : $setStatus;
        }

        // If we are *not* targeting the default locale, but the default locale is published,
        // we will set the published state within the localized version's front-matter.
        if (! $this->isDefaultLocale()) {
            if ($this->attributes['published'] === $published) {
                $this->remove('published');
            } else {
                $this->set('published', $published);
            }
            return;
        }

        $this->attributes['published'] = $published;
    }

    /**
     * Publish the content
     *
     * @return void
     */
    public function publish()
    {
        // If we are *not* targeting the default locale, but the default locale is published,
        // we will set the published state within the localized version's front-matter.
        if (!$this->isDefaultLocale() && $this->attributes['published']) {
            $this->set('published', true);
            return;
        }

        $this->attributes['published'] = true;
    }

    /**
     * Unpublishes the content
     *
     * @return void
     */
    public function unpublish()
    {
        // If we are *not* targeting the default locale, but the default locale is published,
        // we will set the published state within the localized version's front-matter.
        if (!$this->isDefaultLocale() && $this->attributes['published']) {
            $this->set('published', false);
            return;
        }

        $this->attributes['published'] = false;
    }

    /**
     * Add supplemental data to the attributes
     *
     * @return void
     */
    public function supplement()
    {
        $this->supplements['id']        = $this->id();
        $this->supplements['slug']      = $this->slug();
        $this->supplements['url']       = $this->url();
        $this->supplements['uri']       = $this->uri();
        $this->supplements['url_path']  = $this->uri(); // deprecated
        $this->supplements['permalink'] = $this->absoluteUrl();
        $this->supplements['edit_url']  = $this->editUrl();
        $this->supplements['published'] = $this->published();
        $this->supplements['order']     = $this->order();

        if ($this->supplement_taxonomies) {
            // $this->addTaxonomySupplements();
        }
    }

    /**
     * Get data from the cascade (folder.yaml files)
     *
     * @deprecated Use cascadingData instead
     */
    protected function getFolderData()
    {
        return $this->cascadingData();
    }

    /**
     * Gets the URI
     *
     * This is the "identifying URL" for lack of a better description.
     * For instance, where `/fr/blog/my-post` would be a URL, `/blog/my-post` would be the URI.
     *
     * @param string|null $uri
     * @return mixed
     */
    abstract public function uri();

    /**
     * Get the URL
     *
     * @return string
     */
    public function url()
    {
        return URL::makeRelative($this->absoluteUrl());
    }

    /**
     * Get the full, absolute URL
     *
     * @return string
     */
    public function absoluteUrl()
    {
        return URL::makeAbsolute(URL::prependSiteUrl($this->uri(), $this->locale()));
    }

    /**
     * Get or set the template
     *
     * @param string|null $template
     * @return mixed
     */
    abstract public function template($template = null);

    /**
     * Get or set the layout
     *
     * @param string|null $layout
     * @return mixed
     */
    abstract public function layout($layout = null);

    /**
     * Get the folder of the file relative to content path
     *
     * @return string
     */
    abstract public function folder();

    /**
     * Remove any localized data keys that are the identical to the default locale's data.
     *
     * @param array $localized
     * @param array $default
     * @return array
     */
    protected function removeLocalizedDataIdenticalToDefault($localized, $default)
    {
        foreach ($localized as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if ($value === array_get($default, $key)) {
                unset($localized[$key]);
            }
        }

        return $localized;
    }

    /**
     * Normalize the front-matter before saving
     *
     * @param array $data
     * @return array
     */
    protected function normalizeFrontMatter($data)
    {
        return $data;
    }

    /**
     * Perform any necessary operations after a save has been completed
     *
     * @return void
     */
    protected function completeSave()
    {
        //
    }

    /**
     * Get or set the fieldset
     *
     * @param string|null|bool
     * @return \Statamic\Fields\Fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (is_null($fieldset)) {
            return $this->getFieldset();
        }

        $this->set('fieldset', $fieldset);
    }

    /**
     * Get the content type
     *
     * @return string
     */
    public function contentType()
    {
        if ($this instanceof Page) {
            return 'page';
        } elseif ($this instanceof Entry) {
            return 'entry';
        } elseif ($this instanceof Term) {
            return 'term';
        } elseif ($this instanceof GlobalSet) {
            return 'globals';
        }
    }

    /**
     * Delete the content
     *
     * @return mixed
     */
    public function delete()
    {
        // Get all the paths that will need to be deleted. Depending on the content type,
        // more than one file may be deleted. For instance, deleting a page will delete
        // its children. Deleting a default locale will delete its localized versions.
        $paths = $this->getPathsForDeletion();

        // Iterate over the paths and perform the actual deletions. Goodbye, files.
        $paths->each(function ($path) {
            try {
                File::disk('content')->delete($path);
            } catch (FileNotFoundException $e) {
                // Prevent an error if the file doesn't exist.
                // For example, taxonomy terms can exist without a file existing.
            }
        });

        // Subclasses will be able to perform any post-save functionality. For example,
        // renaming a page would leave an empty folder, the Page class will delete it.
        $this->completeDelete();

        // Whoever wants to know about it can do so now.
        $event_class = 'Statamic\Events\Data\\' . ucfirst($this->contentType()) . 'Deleted';
        event(new $event_class($this, $paths->all()));
    }

    /**
     * Get the paths of files to be deleted
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getPathsForDeletion()
    {
        // If we're deleting the default locale, the localized versions will need to
        // go along with it. In this case, we'll build up an array of all the paths.
        if ($this->isDefaultLocale()) {
            return collect($this->locales())->reduce(function ($paths, $locale) {
                return $paths->push($this->localizedPath($locale));
            }, collect());
        }

        // If we're just deleting a localized version, we'll
        // only need the current path, but we still want it formatted as an array.
        return collect([$this->path()]);
    }

    /**
     * Perform any necessary operations after a delete has been completed
     *
     * @return void
     */
    protected function completeDelete()
    {
        //
    }

    /**
     * Whether the data can be taxonomized
     *
     * @return bool
     */
    public function isTaxonomizable()
    {
        return true;
    }

    public function toSearchableArray($fields)
    {
        $array = [];

        foreach ($fields as $field) {
            $array[$field] = method_exists($this, $field) ? $this->$field() : $this->get($field);
        }

        return $array;
    }
}
