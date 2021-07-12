<?php

namespace Statamic\Events;

use Statamic\Entries\Entry;

class EntrySaving extends Event
{
    public $entry;
    private $messageStore;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
        $this->messageStore = app(EntrySavingMessageStore::class);
    }

    public function addSuccessMessage(string $message)
    {
        $this->messageStore->addSuccessMessage($message);
    }

    public function successMessages(): array
    {
        return $this->messageStore->successMessages;
    }

    /**
     * Dispatch the event with the given arguments, and halt on first non-null listener response.
     *
     * @return mixed
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()), [], true);
    }
}
