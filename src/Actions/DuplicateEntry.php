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

    protected function fieldItems()
    {
        if (Site::all()->count() > 1) {
            return [
                'site' => [
                    'type' => 'select',
                    'instructions' => __('Which site should this entry be duplicated to?'),
                    'validate' => 'required|in:all,' . Site::all()->keys()->join(','),
                    'options' => Site::all()->map->name()
                        ->prepend(__('All Sites'), 'all')
                        ->all(),
                    'default' => 'all',
                ],
            ];
        }

        return [];
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry;
    }

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
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
                ->collection($original->collection())
                ->blueprint($original->blueprint()->handle())
                ->locale(isset($values['site']) && $values['site'] !== 'all' ? $values['site'] : $original->locale())
                ->published(false)
                ->slug($slug)
                ->data($data);

            if ($original->hasDate()) {
                $entry->date($original->date());
            }

            $entry->save();

            if ($originalParent && $originalParent !== $original->id()) {
                $entry->structure()
                    ->in(isset($values['site']) && $values['site'] !== 'all' ? $values['site'] : $original->locale())
                    ->appendTo($originalParent->id(), $entry)
                    ->save();
            }

            if (isset($values['site']) && $values['site'] === 'all') {
                Site::all()
                    ->reject(function ($site) use ($entry) {
                        return $site->handle() === $entry->locale();
                    })
                    ->each(function ($site) use ($entry) {
                        $entry->makeLocalization($site->handle())->save();
                    });
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
            ->page($entry->id())
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
        if (Entries::findBySlug($slug, $entry->collection()->handle())) {
            [$title, $slug] = $this->generateTitleAndSlug($entry, $attempt + 1);
        }

        return [$title, $slug];
    }
}
