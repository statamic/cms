<?php

namespace Statamic\Data\Content;

use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\API\Str;
use Statamic\Contracts\Data\Content\PathBuilder as PathBuilderContract;

class PathBuilder implements PathBuilderContract
{
    protected $type = 'page';
    protected $uri;
    protected $slug;
    protected $parent_path;
    protected $collection;
    protected $published = true;
    protected $defaultPublished;
    protected $order;
    protected $extension = 'md';
    protected $locale;

    public function __construct()
    {
        $this->locale = site_locale();
    }

    public function page()
    {
        $this->type = 'page';

        return $this;
    }

    public function entry()
    {
        $this->type = 'entry';

        return $this;
    }

    public function term()
    {
        $this->type = 'term';

        return $this;
    }

    public function uri($uri)
    {
        $this->uri = Str::ensureLeft($uri, '/');

        return $this;
    }

    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function collection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    public function taxonomy($taxonomy)
    {
        return $this->collection($taxonomy);
    }

    public function published($published)
    {
        $this->published = $published;

        return $this;
    }

    public function defaultPublished($published)
    {
        $this->defaultPublished = $published;

        return $this;
    }

    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function extension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function parentPath($path)
    {
        $this->parent_path = $path;

        return $this;
    }

    public function get()
    {
        if (in_array($this->type, ['entry', 'term']) && (! $this->collection || ! $this->slug)) {
            throw new \Exception(sprintf(
                '%s and/or slug have not been specified.',
                $this->type === 'entry' ? 'Collection' : 'Taxonomy'
            ));
        }

        return $this->getFilename();
    }

    private function getFilename()
    {
        if ($this->type == 'page') {
            // pages/_1.slug/index.md
            // pages/_1.slug/fr.index.md
            return URL::tidy(
                $this->getParentPath() . $this->getStatusPrefix() . $this->getOrderPrefix() .
                $this->getSlug() . '/' . $this->getLocalePrefix() . 'index.' . $this->extension
            );
        }

        if ($this->type === 'entry') {
            $path = 'collections/' . $this->collection;

            // collections/blog/_1.slug.md
            // collections/blog/fr/_1.slug.md
            return Path::makeRelative($path) . '/' . $this->getLocalePrefix() .
            $this->getStatusPrefix() . $this->getOrderPrefix() . $this->getSlug() . '.' . $this->extension;
        }

        if ($this->type === 'term') {
            $path = 'taxonomies/'.$this->collection;

            // taxonomies/tags/_1.slug.yaml
            return Path::makeRelative($path) . '/' .
                   $this->getStatusPrefix() . $this->getOrderPrefix() . $this->getSlug() . '.' . $this->extension;
        }

        throw new \Exception('Unexpected type.');
    }

    private function getStatusPrefix()
    {
        if ($this->defaultPublished === false && $this->published) {
            return '_';
        }

        return ($this->published) ? '' : '_';
    }

    private function getOrderPrefix()
    {
        if ($this->order) {
            return $this->order . '.';
        }

        return '';
    }

    private function getLocalePrefix()
    {
        if (! $this->locale || $this->locale == Config::getDefaultLocale()) {
            return '';
        }

        $separator = ($this->type == 'page') ? '.' : '/';

        return $this->locale . $separator;
    }

    private function getSlug()
    {
        if ($this->type == 'page') {
            return URL::slug($this->uri);
        }

        return $this->slug;
    }

    private function getParentPath()
    {
        if ($this->parent_path) {
            $path = Str::ensureRight($this->parent_path, '/');

            $path = preg_replace('/^pages/', '', $path);

            return 'pages/'.$path;
        }

        $path = null;

        if ($this->uri == '/') {
            $path = '';
        }

        if (substr_count($this->uri, '/') > 1) {
            $parent = URL::parent($this->uri);

            if (! $page = Page::whereUri($parent)) {
                throw new \Exception("Parent page [$parent] doesn't exist.");
            }

            $path = $page->path();

            $path = Path::popLastSegment($path) . '/';
        }

        return $path ?: 'pages/'.$path;
    }
}
