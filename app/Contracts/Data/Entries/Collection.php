<?php

namespace Statamic\Contracts\Data\Entries;

use Statamic\Contracts\HasFieldset;
use Statamic\Contracts\Data\DataFolder;

interface Collection extends DataFolder, HasFieldset
{
    /**
     * Get the entries in the folder
     *
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function entries();

    /**
     * Add an entry to the folder
     *
     * @param string $key
     * @param \Statamic\Data\Entry $entry
     */
    public function addEntry($key, $entry);

    /**
     * Remove an entry from the folder
     *
     * @param string $key
     */
    public function removeEntry($key);

    /**
     * Get the number of entries in the folder
     *
     * @return int
     */
    public function count();

    /**
     * Get the collection order
     *
     * @return string
     */
    public function order();

    /**
     * Get or set the route definition
     *
     * @return string
     */
    public function route($route = null);
}
