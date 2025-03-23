<?php

namespace Statamic\CommandPalette;

use Statamic\Facades\CP\Nav;
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
            ->buildNavigation()
            ->buildActions()
            ->buildHistory()
            ->sort()
            ->toArray();
    }

    protected function buildNavigation(): self
    {
        // TODO:
        // Use same blink cache as NavComposer?
        // Do we also want to show/cache resolved item children?

        Nav::build()
            ->flatMap(fn ($section) => $section['items'])
            ->each(fn ($item) => $this->addCommand(
                (new Link(
                    text: __($item->section()).' > '.__($item->display()),
                    category: Category::Navigation,
                ))->url($item->url())
            ));

        return $this;
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
