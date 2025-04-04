<?php

namespace Statamic\CommandPalette;

use Illuminate\Support\Collection;
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

    public function build(): Collection
    {
        return $this
            ->buildActions()
            ->buildHistory()
            ->get();
    }

    protected function buildActions(): self
    {
        $this->addCommand(
            (new Link(
                text: 'Save',
                category: Category::Actions,
            ))->url('/cp')
        );

        $this->addCommand(
            (new Link(
                text: 'Duplicate',
                category: Category::Actions,
            ))->url('/cp')
        );

        $this->addCommand(
            (new Link(
                text: 'Delete',
                category: Category::Actions,
            ))->url('/cp')
        );

        return $this;
    }

    protected function buildHistory(): self
    {
        // TODO: Set up ajax route for caching command palette history as user runs commands.

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

    public function get(): Collection
    {
        return $this->items;
    }
}
