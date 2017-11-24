<?php

namespace Statamic\Data\Globals;

use Statamic\API\Fieldset;
use Statamic\API\Path;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalContract;
use Statamic\Data\Content\Content;

class GlobalSet extends Content implements GlobalContract
{
    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    public function slug($slug = null)
    {
        if (is_null($slug)) {
            return $this->attributes['slug'];
        }

        $this->attributes['slug'] = $slug;
    }

    /**
     * Get or set the path
     *
     * @param string|null $path
     * @return string
     */
    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->getPath();
        }

        $this->attributes['path'] = $path;
    }

    /**
     * Get the path to a localized version
     *
     * @param string $locale
     * @return string
     */
    public function localizedPath($locale)
    {
        return $this->buildPath($locale);
    }

    /**
     * Get the path to the file
     *
     * @return string
     */
    protected function getPath()
    {
        if (isset($this->attributes['path'])) {
            return $this->attributes['path'];
        }

        return $this->buildPath();
    }

    /**
     * Dynamically build the file path
     *
     * @param string $locale
     * @return string
     */
    protected function buildPath($locale = null)
    {
        $locale = $locale ?: $this->locale();

        $locale_prefix = ($locale === default_locale()) ? '' : $locale . '/';

        return Path::makeRelative('globals/' . $locale_prefix . $this->slug() . '.yaml');
    }

    /**
     * Get the path before the object was modified.
     *
     * @return string
     */
    public function originalPath()
    {
        // TODO: Implement originalPath() method.
    }

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    public function originalLocalizedPath($locale)
    {
        return $this->buildPath($locale);
    }

    /**
     * Get or set the title
     *
     * @param string|null $title
     * @return mixed
     */
    public function title($title = null)
    {
        if (! is_null($title)) {
            $this->set('title', $title);
        }

        if ($title === false) {
            $this->set('title', null);
        }

        $fallback = ($this->slug() === 'global')
            ? translate('cp.general_globals')
            : ucfirst($this->slug());

        return $this->get('title', $fallback);
    }

    /**
     * Get or set the URI
     *
     * This is the "identifying URL" for lack of a better description.
     * For instance, where `/fr/blog/my-post` would be a URL, `/blog/my-post` would be the URI.
     *
     * @param string|null $uri
     * @return mixed
     * @throws \Exception
     */
    public function uri($uri = null)
    {
        // Globals don't have URIs
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('globals.edit', $this->slug());
    }

    /**
     * Get or set the template
     *
     * @param string|null $template
     * @return mixed
     * @throws \Exception
     */
    public function template($template = null)
    {
        throw new \Exception('Globals cannot have templates.');
    }

    /**
     * Get or set the layout
     *
     * @param string|null $layout
     * @return mixed
     * @throws \Exception
     */
    public function layout($layout = null)
    {
        throw new \Exception('Globals cannot have layouts.');
    }

    /**
     * Get the folder of the file relative to content path
     *
     * @return string
     */
    public function folder()
    {
        return Path::directory($this->path());
    }

    /**
     * Get the fieldset
     *
     * @return string|null
     */
    protected function getFieldset()
    {
        $fieldset = $this->getWithCascade('fieldset', 'globals');

        $fieldset = Fieldset::get($fieldset);

        $fieldset->type('global');

        return $fieldset;
    }
}