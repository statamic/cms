<?php

namespace Statamic\Contracts\Data\Entries;

use Statamic\Contracts\Data\Content\ContentFactory;

interface EntryFactory extends ContentFactory
{
    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug);

    /**
     * @param string $collection
     * @return $this
     */
    public function collection($collection);
}
