<?php

namespace Statamic\Events;

class EntrySavingMessageStore
{
    protected $successMessages = [];

    public function addSuccessMessage(string $message)
    {
        $this->successMessages[] = $message;
    }

    public function successMessages(): array
    {
        return $this->successMessages;
    }
}
