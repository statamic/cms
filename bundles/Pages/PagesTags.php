<?php

namespace Statamic\Addons\Pages;

use Statamic\API\Page;
use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\Addons\Collection\CollectionTags;

class PagesTags extends CollectionTags
{
    /**
     * Maps to `{{ pages }}`
     *
     * @return string
     */
    public function index()
    {
        $depth = $this->getInt('depth', 1);

        $this->collection = $this->getPage()->children($depth);

        // Convert taxonomy fields to actual taxonomy terms.
        // This will allow taxonomy term data to be available in the template without additional tags.
        // If terms are not needed, there's a slight performance benefit in disabling this.
        if ($this->getBool('supplement_taxonomies', true)) {
            $this->collection = $this->collection->supplementTaxonomies();
        }

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    /**
     * Get the source page from which to grab pages
     *
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    private function getPage()
    {
        if ($id = $this->get(['id', 'from_id'])) {
            return Page::find($id);
        }

        $from = $this->get(['from', 'folder', 'url'], URL::getCurrent());

        // First check for a URI as-is
        if (Page::uriExists($from)) {
            return Page::whereUri($from);
        }

        // If that didn't work, try prepending a slash
        $from_slashed = Str::ensureLeft($from, '/');
        if (Page::uriExists($from_slashed)) {
            return Page::whereUri($from_slashed);
        }

        // Finally try an id
        return Page::find($from);
    }

    /**
     * Alias of `{{ pages }}`
     *
     * @param string $method
     * @param array  $args
     * @return string
     **/
    public function listing()
    {
        return $this->index();
    }

    /**
     * Maps to `{{ pages:next }}`
     *
     * @return string
     */
    public function next()
    {
        $this->collectSequence();

        return $this->sequence('next');
    }

    /**
     * Maps to `{{ pages:previous }}`
     *
     * @return string
     */
    public function previous()
    {
        $this->collectSequence();

        return $this->sequence('previous');
    }

    /**
     * Set the collection for a sequence
     */
    private function collectSequence()
    {
        $from = $this->get(['from', 'folder', 'url'], URL::parent(URL::getCurrent()));

        $from = Str::ensureLeft($from, '/');

        $this->collection = Page::whereUri($from)->children(1);
    }

    /**
     * Get the sort order of the collection
     *
     * @return string
     */
    protected function getSortOrder()
    {
        return $this->get('sort', 'order|title');
    }

    /**
     * Maps to `{{ pages:count }}`
     *
     * @return integer
     */
    public function count()
    {
        $from = $this->get(['from', 'folder', 'url']);

        $from = Str::ensureLeft($from, '/');

        $this->collection = Page::whereUri($from)->children(1);

        $this->filter();

        return $this->collection->count();
    }
}
