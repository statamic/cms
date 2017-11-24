<?php

namespace Statamic\Contracts\Data\Entries;

use Statamic\Contracts\Data\Content\Content;

interface Entry extends Content
{
    /**
     * Get or set the associated collection
     *
     * @param Collection|string|null $collection
     * @return Collection
     */
    public function collection($collection = null);

    /**
     * Get or set the name of the associated collection
     *
     * @param string|null $name
     * @return string
     */
    public function collectionName($name = null);

    /**
     * Get the order type (date, number, alphabetical)
     *
     * @return string
     */
    public function orderType();

    /**
     * Get the entry's date
     *
     * @return \Carbon\Carbon
     * @throws \Statamic\Exceptions\InvalidEntryTypeException
     */
    public function date();

    /**
     * Does the entry have a timestamp?
     *
     * @return bool
     */
    public function hasTime();
}
