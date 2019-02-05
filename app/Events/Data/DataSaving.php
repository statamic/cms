<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;
use Statamic\Contracts\Data\DataSavingEvent;

class DataSaving extends Event implements DataSavingEvent
{
    /**
     * @var \Statamic\Data\Data
     */
    protected $data;

    /**
     * @param \Statamic\Data\Data $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get data related to event.
     *
     * @return \Statamic\Data\Data
     */
    public function data()
    {
        return $this->data;
    }
}
