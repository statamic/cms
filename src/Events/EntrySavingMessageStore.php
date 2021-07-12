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

    public function getMessage(): string
    {
        $successMessages = $this->successMessages();
        if (empty($successMessages)) {
            return __('Saved');
        } else {
            return join(PHP_EOL, $successMessages);
        }
    }
}
