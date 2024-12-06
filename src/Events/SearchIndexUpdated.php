<?php

namespace Statamic\Events;

class SearchIndexUpdated extends Event
{
    public function __construct(public $index)
    {
    }
}
