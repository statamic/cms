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

    /**
     * Dispatch the event with the given arguments, and halt on first non-null listener response.
     *
     * @return mixed
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()), [], true);
    }
}
