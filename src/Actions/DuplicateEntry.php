<?php

namespace Statamic\Actions;

use Illuminate\Support\Str;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class DuplicateEntry extends Action
{
    private $newItems;

    public static function title()
    {
        return __('Duplicate');
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry;
    }

    public function confirmationText()
    {
        $hasDescendants = Site::hasMultiple() && $this->items
            ->map(fn ($entry) => $entry->hasOrigin() ? $entry->root() : $entry)
            ->unique()
            ->contains(fn ($entry) => $entry->descendants()->count());

        if ($hasDescendants) {
            /** @translation */
            return 'statamic::messages.duplicate_action_localizations_confirmation';
        }

        return parent::confirmationText();
    }

    public function warningText()
    {
        if ($this->items->contains(fn ($entry) => $entry->hasOrigin())) {
            if ($this->items->count() === 1) {
                /** @translation */
                return 'statamic::messages.duplicate_action_warning_localization';
            }

            /** @translation */
            return 'statamic::messages.duplicate_action_warning_localizations';
        }
    }

    public function dirtyWarningText()
    {
        /** @translation */
        return 'Any unsaved changes will not be duplicated into the new entry.';
    }

    public function run($items, $values)
    {
        $this->newItems = $items
            ->map(fn ($entry) => $entry->hasOrigin() ? $entry->root() : $entry)
            ->unique()
            ->map(fn ($original) => $this->duplicateEntry($original));
    }

    private function duplicateEntry(Entry $original, ?string $origin = null)
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

        $original
            ->directDescendants()
            ->filter(fn ($entry) => User::current()->can('create', [Entry::class, $entry->collection(), $entry->site()]))
            ->each(function ($descendant) use ($entry) {
                $this->duplicateEntry($descendant, origin: $entry->id());
            });

        if ($originalParent && $originalParent !== $original->id()) {
            $entry->structure()
                ->in($original->locale())
                ->appendTo($originalParent->id(), $entry)
                ->save();
        }

        return $entry;
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
        return $user->can('create', [Entry::class, $item->collection(), $item->site()]);
    }

    public function redirect($items, $values)
    {
        if ($this->context['view'] !== 'form') {
            return;
        }

        return $this->newItems->first()->editUrl();
    }
}
