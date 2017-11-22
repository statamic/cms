<?php

namespace Statamic\Events;

use Statamic\Contracts\Data\Data;

class DataIdCreated extends Event
{
    /**
     * @var \Statamic\Contracts\Data\Data
     */
    public $data;

    /**
     * Create a new event instance
     *
     * @param \Statamic\Contracts\Data\Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }
}
