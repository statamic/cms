<?php

namespace Statamic\Events;

class StatamicUpdated extends Event
{
    /**
     * The new/current version of Statamic.
     *
     * @var string
     */
    public $version;

    /**
     * The version Statamic was updated from.
     *
     * @var string
     */
    public $previousVersion;

    /**
     * Create a new event instance
     */
    public function __construct($to, $from)
    {
        $this->version = $to;
        $this->previousVersion = $from;
    }
}
