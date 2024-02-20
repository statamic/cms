<?php

namespace Statamic\Exceptions;

class EntryNotFoundException extends \Exception
{
    protected string|int $id;

    public function __construct($id)
    {
        parent::__construct("No entry results for id {$id}");
    }
}
