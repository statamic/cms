<?php

namespace Statamic\Search;

use Statamic\API\Page;
use Statamic\API\Term;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Collection;

class ItemResolver
{
    /**
     * @var Index
     */
    private $index;

    /**
     * Set the index.
     *
     * @param Index $index
     * @return $this
     */
    public function setIndex(Index $index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get the fields that should be indexed.
     *
     * @return mixed
     */
    public function getFields()
    {
        if ($this->isDefaultIndex()) {
            return Config::get('search.searchable');
        }

        list($type, $handle) = explode('/', $this->index->name());

        if ($type === 'collections') {
            return Collection::whereHandle($handle)->get('searchable');
        }
    }

    /**
     * Get the items that should be indexed.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItems()
    {
        if ($this->isDefaultIndex()) {
            return Entry::all()->merge(Page::all())->merge(Term::all());
        }

        list($type, $handle) = explode('/', $this->index->name());

        if ($type === 'collections') {
            $collection = Collection::whereHandle($handle);
            return $collection->get('searchable') ? $collection->entries() : collect();
        }
    }

    /**
     * Whether the index is the default.
     *
     * @return bool
     */
    protected function isDefaultIndex()
    {
        return $this->index->name() === Config::get('search.default_index');
    }
}
