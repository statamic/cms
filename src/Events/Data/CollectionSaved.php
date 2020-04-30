<?php

namespace Statamic\Events\Data;

use Statamic\Contracts\Data\DataSavedEvent;
use Statamic\Entries\Collection;
use Statamic\Events\Event;
use Statamic\Facades\Path;

class CollectionSaved extends Event implements DataSavedEvent
{
    /**
     * @var Collection
     */
    public $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get contextual data related to event.
     *
     * @return array
     */
    public function contextualData()
    {
        return $this->collection->data();
    }

    /**
     * Get paths affected by event.
     *
     * @return array
     */
    public function affectedPaths()
    {
        return [
            Path::makeFull($this->collection->yamlPath()),
            settings_path('routes.yaml'),
        ];
    }
}
