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

    protected function fieldItems()
    {
        if (Site::hasMultiple()) {
            return [
                'mode' => [
                    'display' => __('Mode'),
                    'type' => 'button_group',
                    'instructions' => __('Should this entry be duplicated to just the current site or to all sites?'),
                    'options' => [
                        'all' => __('All Sites'),
                        'current' => __('Current Site'),
                    ],
                    'default' => 'all',
                    'validate' => 'required',
                ],
            ];
        }

        return [];
    }

    public function run($items, $values)
    {
        $items->each(function (Entry $original) use ($values) {
            $originalParent = $this->getEntryParentFromStructure($original);
            [$title, $slug] = $this->generateTitleAndSlug($original);

            $data = $original
                ->data()
                ->except($original->blueprint()->fields()->all()->reject->shouldBeDuplicated()->keys())
                ->merge(['title' => $title,
                    'duplicated_from' => $original->id(),
                ])->all();

            $entry = Entries::make()
                ->locale($values['site'] ?? Site::current()->handle())
                ->collection($original->collection())
                ->blueprint($original->blueprint()->handle())
                ->published(false)
                ->data($data);

            if (isset($values['origin'])) {
                $entry->origin($values['origin']);
            }

            if ($original->collection()->requiresSlugs()) {
                $entry->slug($slug);
            }

            if ($original->hasDate()) {
                $entry->date($original->date());
            }

            $entry->save();

            if (isset($values['mode']) && $values['mode'] === 'all') {
                $original->descendants()->each(function ($descendant) use ($entry) {
                    $this->run(collect([$descendant]), collect([
                        'site' => $descendant->locale(),
                        'origin' => $entry,
                    ]));
                });
            }

            if ($originalParent && $originalParent !== $original->id()) {
                $entry->structure()
                    ->in($original->locale())
                    ->appendTo($originalParent->id(), $entry)
                    ->save();
            }
        });
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
        $title = $entry->get('title');
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
