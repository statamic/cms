<?php

namespace Statamic\CommandPalette;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
        // TODO: We need to bust this cache when content or nav changes
        // TODO: Cache per user
        // return Cache::rememberForever('statamic-command-palette', function () {
        return $this
            ->buildNav()
            ->buildFields()
            ->get();
        // });
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

        Facades\AssetContainer::all()
            ->map(fn ($container) => $container->blueprintCommandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

        Facades\GlobalSet::all()
            ->map(fn ($set) => $set->blueprintCommandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

        Facades\Form::all()
            ->map(fn ($form) => $form->blueprintCommandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

        $this->addCommand(Facades\User::blueprintCommandPaletteLink());
        $this->addCommand(Facades\UserGroup::blueprintCommandPaletteLink());

        Facades\Blueprint::getRenderableAdditionalNamespaces()
            ->flatMap(fn ($namespace) => $namespace['blueprints']->map(fn ($renderable) => $renderable['command_palette_link']))
            ->each(fn (Link $link) => $this->addCommand($link));

        Facades\Fieldset::all()
            ->map(fn (Fieldset $fieldset) => $fieldset->commandPaletteLink())
            ->each(fn (Link $link) => $this->addCommand($link));

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
