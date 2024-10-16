<?php

namespace Statamic\Events;

use Illuminate\Foundation\Events\Dispatchable;

class EntryScheduleFulfilled
{
    use Dispatchable;

    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }
}
