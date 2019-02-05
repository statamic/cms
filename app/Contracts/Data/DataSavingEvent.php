<?php

namespace Statamic\Contracts\Data;

interface DataSavingEvent
{
    /**
     * Get data related to event.
     *
     * @return mixed
     */
    public function data();
}
