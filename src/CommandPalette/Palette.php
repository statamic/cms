<?php

namespace Statamic\CommandPalette;

use Statamic\Support\Arr;

class Palette
{
    protected $items;

    public function __construct()
    {
        $this->items = collect();
    }

    public function addCommand(Command $command): self
    {
        $this->items->push(
            $this->validateCommandArray($command->toArray()),
        );

        return $this;
    }

    public function build(): array
    {
        return $this
            ->buildActions()
            ->buildHistory()
            ->sort()
            ->toArray();
    }

    protected function buildActions(): self
    {
        return $this;
    }

    protected function buildHistory(): self
    {
        return $this;
    }

    protected function sort(): self
    {
        // TODO: Sort categories? Or sort in JS?

        return $this;
    }

    public function validateCommandArray(array $command): array
    {
        throw_unless(is_string(Arr::get($command, 'type')), new \Exception('Must output command [type] string!'));
        throw_unless(is_string(Arr::get($command, 'category')), new \Exception('Must output command [category] string!'));
        throw_unless(is_string(Arr::get($command, 'text')), new \Exception('Must output command [text] string!'));

        return $command;
    }

    public function toArray(): array
    {
        return $this->items->toArray();
    }
}
