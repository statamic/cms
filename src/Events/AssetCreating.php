<?php

namespace Statamic\Events;

class AssetCreating extends Event
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
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
