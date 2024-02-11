<?php

namespace Statamic\Exceptions;

class EntryNotFoundException extends \Exception
{
    protected string|int $id;

    public function setEntry(string|int $id): object
    {
        $this->message = "No entry results for id {$id}";

        return $this;
    }
}