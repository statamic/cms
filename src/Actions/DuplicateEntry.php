<?php

namespace Statamic\Actions;

use Illuminate\Support\Str;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;
use Statamic\Facades\Site;

class DuplicateEntry extends Action
{
    public static function title()
    {
        return __('Duplicate');
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry;
    }

    public function warningText()
    {
        if ($this->items->contains(fn ($entry) => $entry->hasOrigin())) {
            return $this->items->count() === 1
                ? 'duplicate_action_warning_localization'
                : 'duplicate_action_warning_localizations';
        }
    }

    protected function fieldItems()
    {
        if (! Site::hasMultiple()) {
            return [];
        }

        // If none of the selected entries have localizations, don't bother showing the field.
        $hasLocalizations = $this->items
            ->map(fn ($entry) => $entry->hasOrigin() ? $entry->root() : $entry)
            ->contains(fn ($entry) => $entry->descendants()->count());

        if (! $hasLocalizations) {
            return [];
        }

        return [
            'descendants' => [
                'display' => __('statamic::messages.duplicate_action_descendants_display'),
                'type' => 'toggle',
                'inline_label' => __('statamic::messages.duplicate_action_descendants_false'),
                'inline_label_when_true' => __('statamic::messages.duplicate_action_descendants_true'),
                'default' => true,
                'validate' => 'required',
            ],
        ];
    }

    public function run($items, $values)
    {
        $this->duplicateEntries(
            $items,
            $values['descendants'] ?? false
        );
    }

    private function duplicateEntries($entries, bool $withDescendants)
    {
        $entries
            ->map(fn ($entry) => $entry->hasOrigin() ? $entry->root() : $entry)
            ->unique()
            ->each(fn (Entry $original) => $this->duplicateEntry($original, $withDescendants));
    }

    private function duplicateEntry(Entry $original, bool $withDescendants, string $origin = null)
    {
        $originalParent = $this->getEntryParentFromStructure($original);
        [$title, $slug] = $this->generateTitleAndSlug($original);

        $data = $original
            ->data()
            ->except($original->blueprint()->fields()->all()->reject->shouldBeDuplicated()->keys())
            ->merge(['title' => $title,
                'duplicated_from' => $original->id(),
            ])->all();

        $entry = Entries::make()
            ->locale($original->locale())
            ->collection($original->collection())
            ->blueprint($original->blueprint()->handle())
            ->published(false)
            ->data($data)
            ->origin($origin);

        if ($original->collection()->requiresSlugs()) {
            $entry->slug($slug);
        }

        if ($original->hasDate()) {
            $entry->date($original->date());
        }

        $entry->save();

        if ($withDescendants) {
            $original->descendants()->each(function ($descendant) use ($entry) {
                $this->duplicateEntry($descendant, withDescendants: true, origin: $entry->id());
            });
        }

        if ($originalParent && $originalParent !== $original->id()) {
            $entry->structure()
                ->in($original->locale())
                ->appendTo($originalParent->id(), $entry)
                ->save();
        }
    }

    protected function getEntryParentFromStructure(Entry $entry)
    {
        if (! $entry->structure()) {
            return null;
        }

        $parentEntry = $entry
            ->structure()
            ->in($entry->locale())
            ->find($entry->id())
            ->parent();

        if (! $parentEntry) {
            return null;
        }

        if ($entry->structure()->expectsRoot() && $entry->structure()->in($entry->locale())->root()['entry'] === $parentEntry->id()) {
            return null;
        }

        return $parentEntry;
    }

    protected function generateTitleAndSlug(Entry $entry, $attempt = 1)
    {
        $title = $entry->value('title');
        $slug = $entry->slug();
        $suffix = ' ('.__('Duplicated').')';

        if ($attempt == 1) {
            $title = $title.$suffix;
        }

        if ($attempt !== 1) {
            if (! Str::contains($title, $suffix)) {
                $title .= $suffix;
            }

            $title .= ' ('.$attempt.')';
        }

        $slug .= '-'.$attempt;

        // If the slug we've just built already exists, we'll try again, recursively.
        if ($entry->collection()->queryEntries()->where('locale', $entry->locale())->where('slug', $slug)->count()) {
            [$title, $slug] = $this->generateTitleAndSlug($entry, $attempt + 1);
        }

        return [$title, $slug];
    }

    public function authorize($user, $item)
    {
        return $user->can('create', [Entry::class, $item->collection()]);
    }
}
