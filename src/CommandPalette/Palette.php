<?php

namespace Statamic\CommandPalette;

use Illuminate\Support\Collection;
use Statamic\CP\Navigation\NavItem;
use Statamic\Facades;
use Statamic\Fields\Fieldset;
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
            ->buildNav()
            ->buildFields()
            ->buildActions()
            ->get();
    }

    protected function buildNav(): self
    {
        Facades\CP\Nav::build()
            ->flatMap(fn (array $section) => $section['items'])
            ->filter(fn (NavItem $item) => $item->url())
            ->flatMap(fn (NavItem $item) => $item->generateCommandPaletteLinks())
            ->each(fn (Link $link) => $this->addCommand($link));

        return $this;
    }

    protected function buildFields(): self
    {
        if (Facades\User::current()->cant('configure fields')) {
            return $this;
        }

        Facades\Fieldset::all()
            ->map(fn (Fieldset $fieldset) => $fieldset->generateCommandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

        return $this;
    }

    protected function buildActions(): self
    {
        // TODO: Addressing actions in separate PR.

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
