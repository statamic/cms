<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

class DataSaving extends Event
{
    /**
     * @var \Statamic\Data\Data
     */
    public $data;

    /**
     * @param \Statamic\Data\Data $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}
