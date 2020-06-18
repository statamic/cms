<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;
use Statamic\Support\Str;

abstract class Deleted extends Event
{
    public $item;

    /**
     * Instantiate deleted event.
     *
     * @param mixed $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        $class = (new \ReflectionClass($this))->getShortName();

        $noun = str_replace('Deleted', '', $class);
        $noun = Str::snake($noun, ' ');

        return __(':item deleted.', ['item' => ucfirst($noun)]);
    }
}
