<?php

namespace Statamic\Actions;

use Illuminate\Support\Str;
use Statamic\Contracts\Entries\Entry as AnEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

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
                    'validate' => 'required|in:all,'.Site::all()->keys()->join(','),
                    'options' => Site::all()
                        ->map(function (SitesSite $site) {
                            return $site->name();
                        })
                        ->prepend(__('All Sites'), 'all')
                        ->toArray(),
                    'default' => 'all',
                ],
            ];
        }

        return [];
    }

    public function visibleTo($item)
    {
        return $item instanceof AnEntry;
    }

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($item) use ($values) {
                /** @var \Statamic\Entries\Entry $item */
                if ($item instanceof AnEntry) {
                    $itemParent = $this->getEntryParentFromStructure($item);
                    $itemTitleAndSlug = $this->generateTitleAndSlug($item);

                    $entry = Entry::make()
                        ->collection($item->collection())
                        ->blueprint($item->blueprint()->handle())
                        ->locale(isset($values['site']) && $values['site'] !== 'all' ? $values['site'] : $item->locale())
                        ->published(config('statamic.duplicator.defaults.published', $item->published()))
                        ->slug($itemTitleAndSlug['slug'])
                        ->data(
                            $item->data()
                                ->except(config("duplicator.ignored_fields.entries.{$item->collectionHandle()}"))
                                ->merge([
                                    'title' => $itemTitleAndSlug['title'],
                                ])
                                ->toArray()
                        );

                    if ($item->hasDate()) {
                        $entry->date($item->date());
                    }

                    if (config('statamic.duplicator.fingerprint') === true) {
                        $entry->set('is_duplicate', true);
                    }

                    $entry->save();

                    if ($itemParent && $itemParent !== $item->id()) {
                        $entry->structure()
                            ->in(isset($values['site']) && $values['site'] !== 'all' ? $values['site'] : $item->locale())
                            ->appendTo($itemParent->id(), $entry)
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
                }
            });
    }

    protected function getEntryParentFromStructure(AnEntry $entry)
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

    // This method has been copied over from Statamic v2 - it's been updated to work with v3.
    protected function generateTitleAndSlug(AnEntry $entry, $attempt = 1)
    {
        $title = $entry->get('title');
        $slug = $entry->slug();

        if ($attempt == 1) {
            $title = $title.__(' (Duplicated)');
        }

        if ($attempt !== 1) {
            if (! Str::contains($title, __(' (Duplicated)'))) {
                $title .= __(' (Duplicated)');
            }

            $title .= ' ('.$attempt.')';
        }

        $slug .= '-'.$attempt;

        // If the slug we've just built already exists, we'll try again, recursively.
        if (Entry::findBySlug($slug, $entry->collection()->handle())) {
            $generate = $this->generateTitleAndSlug($entry, $attempt + 1);

            $title = $generate['title'];
            $slug = $generate['slug'];
        }

        return [
            'title' => $title,
            'slug' => $slug,
        ];
    }
}
