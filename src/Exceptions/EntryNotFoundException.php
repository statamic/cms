<?php

namespace Statamic\Exceptions;

class EntryNotFoundException extends \Exception
{
    protected $entry;

    public function __construct($entry)
    {
        parent::__construct("Entry [{$entry}] not found");

        $this->entry = $entry;
    }
}
