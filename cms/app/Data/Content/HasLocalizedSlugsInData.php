<?php

namespace Statamic\Data\Content;

trait HasLocalizedSlugsInData
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
            return $this->getSlug();
        }

        $this->setSlug($slug);
    }

    /**
     * Get the slug
     *
     * @return string
     */
    protected function getSlug()
    {
        // For localized versions, the slug is contained within the data.
        if (! $this->isDefaultLocale() && $this->has('slug')) {
            return $this->get('slug');
        }

        // Remove any hidden/draft indicators.
        return ltrim($this->attributes['slug'], '_');
    }

    /**
     * Set the slug
     *
     * @param $slug
     */
    protected function setSlug($slug)
    {
        if ($this->isDefaultLocale()) {
            // If this content belongs to the default locale, we want to update
            // the slug property. It is not stored in the front matter.
            $this->attributes['slug'] = $slug;
        } else {
            // If this is not the default locale, we want to store the slug in the
            // front-matter and leave the property as-is. Also, we only need to
            // store the slug if it's different from the default locale slug.
            if ($slug !== $this->get('slug')) {
                $this->set('slug', $slug);
            }
        }
    }
}