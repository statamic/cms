<?php

namespace Statamic\Entries;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Blink;

class InitiatorStack
{
    private $entry;
    private $key;
    private $stack;

    public function entry($entry)
    {
        $this->entry = $entry;
        $this->key = 'entry-event-initiator-'.$entry->root()->id();
        $this->stack = Blink::get($this->key) ?? collect();

        return $this;
    }

    public function push()
    {
        $initiator = $this->stack->first() ?? $this->entry;

        $initiatorIsAncestor = $this->entry
            ->ancestors()
            ->contains(fn ($entry) => $entry->id() === $initiator->id());

        if ($this->stack->isEmpty() || $initiatorIsAncestor) {
            $this->stack->push($this->entry);
            Blink::put($this->key, $this->stack);
        }

        return $this;
    }

    public function initiator(): ?Entry
    {
        return $this->stack->first();
    }

    public function pop()
    {
        $this->stack->pop();
    }
}
