<?php


namespace Statamic\Events;


class EntrySavingMessageStore
{
    protected $successMessages = [];

    function addSuccessMessage(string $message)
    {
        $this->successMessages[] = $message;
    }

    function successMessages(): array
    {
        return $this->successMessages;
    }
}
