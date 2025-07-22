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
            // ->buildNav()
            ->buildFields()
            ->buildActions()
            ->get();
    }

    protected function buildNav(): self
    {
        Facades\CP\Nav::build()
            ->flatMap(fn (array $section) => $section['items'])
            ->filter(fn (NavItem $item) => $item->url())
            ->flatMap(fn (NavItem $item) => $item->commandPaletteLinks())
            ->each(fn (Link $link) => $this->addCommand($link));

        return $this;
    }

    protected function buildFields(): self
    {
        if (Facades\User::current()->cant('configure fields')) {
            return $this;
        }

        Facades\Collection::all()
            ->flatMap(fn ($collection) => $collection->entryBlueprintCommandPaletteLinks())
            ->each(fn (Link $link) => $this->addCommand($link));

        Facades\Taxonomy::all()
            ->flatMap(fn ($taxonomy) => $taxonomy->termBlueprintCommandPaletteLinks())
            ->each(fn (Link $link) => $this->addCommand($link));

        Facades\Nav::all()
            ->map(fn ($nav) => $nav->blueprintCommandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

        Facades\GlobalSet::all()
            ->map(fn ($set) => $set->blueprintCommandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

        // TODO: Womp, got to end of this and realized they don't have `editUrl()` methods, so we'll refactor this to what's above ^
        // collect()
        //     ->merge(Facades\Collection::all()->flatMap(fn ($collection) => $collection->entryBlueprints()))
        //     ->merge(Facades\Taxonomy::all()->flatMap(fn ($taxonomy) => $taxonomy->termBlueprints()))
        //     ->merge(Facades\Nav::all()->map->blueprint())
        //     ->merge(Facades\GlobalSet::all()->map->blueprint())
        //     ->merge(Facades\AssetContainer::all()->map->blueprint())
        //     ->merge(Blueprint::getAdditionalNamespaces()->keys()->flatMap(fn (string $key) => Blueprint::in($key)->sortBy(fn (Blueprint $blueprint) => $blueprint->title())))
        //     ->flatten()
        //     ->map(fn (Blueprint $blueprint) => $blueprint->generateCommandPaletteLink())
        //     ->each(fn (Link $link) => $this->addCommand($link));

        Facades\Fieldset::all()
            ->map(fn (Fieldset $fieldset) => $fieldset->commandPaletteLink())
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
