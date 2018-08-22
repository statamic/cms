<?php

namespace Statamic\Contracts\Data;

interface DataEvent
{
    /**
     * Get contextual data related to event.
     *
     * @return array
     */
    public function contextualData();

    /**
     * Get paths affected by event.
     *
     * @return array
     */
    public function affectedPaths();
}
