<?php

namespace Statamic\Extend;

/**
 * Performs actions when events are emitted
 */
abstract class Listener
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Mapping of event to method names to be registered
     * @var array
     */
    public $events = [];

    /**
     * Create a new Listener instance
     */
    public function __construct()
    {
        $this->bootstrap();
        $this->init();
    }
}
