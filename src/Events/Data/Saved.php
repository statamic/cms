<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;
use Statamic\Support\Str;

abstract class Saved extends Event
{
    public $item;

    /**
     * Instantiate saved event.
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

        $noun = str_replace('Saved', '', $class);
        $noun = Str::snake($noun, ' ');

        return __(':item saved.', ['item' => ucfirst($noun)]);
    }
}
