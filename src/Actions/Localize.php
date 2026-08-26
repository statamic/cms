<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;

use function Statamic\trans as __;
use function Statamic\trans_choice;

class Localize extends Action
{
    protected $icon = 'earth';

    public static function title()
    {
        return __('Localize');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list'
            && $item instanceof Entry
            && Site::multiEnabled()
            && $item->collection()->sites()->count() > 1
            && $this->missingSites($item)->isNotEmpty();
    }

    public function visibleToBulk($items)
    {
        if ($items->whereInstanceOf(Entry::class)->count() !== $items->count()) {
            return false;
        }

        if (! Site::multiEnabled()) {
            return false;
        }

        if ($items->map->collectionHandle()->unique()->count() !== 1) {
            return false;
        }

        $collection = $items->first()->collection();

        if ($collection->sites()->count() <= 1) {
            return false;
        }

        return $items->contains(fn (Entry $entry) => $this->missingSites($entry)->isNotEmpty());
    }

    public function authorize($user, $entry)
    {
        return $user->can('edit', $entry);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Localize this entry to the selected site? Existing localizations will be skipped.|Localize these :count entries to the selected site? Existing localizations will be skipped.';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Localize|Localize :count Entries';
    }

    public function run($entries, $values)
    {
        $site = $values['site'];
        $created = 0;
        $skipped = 0;

        $entries->each(function (Entry $entry) use ($site, &$created, &$skipped) {
            if ($entry->locale() === $site || $entry->existsIn($site)) {
                $skipped++;

                return;
            }

            if (! User::current()->can('edit', $entry)) {
                $skipped++;

                return;
            }

            $entry->makeLocalization($site)->store(['user' => User::current()]);
            $created++;
        });

        if ($created === 0) {
            /** @translation */
            return __('No localizations were created.');
        }

        if ($skipped > 0) {
            return __('Created :created, skipped :skipped.', [
                'created' => $created,
                'skipped' => $skipped,
            ]);
        }

        return trans_choice('Created :count localization|Created :count localizations', $created, [
            'count' => $created,
        ]);
    }

    protected function fieldItems()
    {
        return [
            'site' => [
                'display' => __('Site'),
                'hide_display' => true,
                'type' => 'select',
                'placeholder' => __('Choose a site...'),
                'options' => $this->siteOptions(),
                'validate' => 'required',
            ],
        ];
    }

    private function siteOptions(): array
    {
        $collection = $this->items->first()?->collection();

        if (! $collection) {
            return [];
        }

        return $collection->sites()
            ->filter(fn ($handle) => $this->items->contains(
                fn (Entry $entry) => $entry->locale() !== $handle && ! $entry->existsIn($handle)
            ))
            ->mapWithKeys(fn ($handle) => [
                $handle => Site::get($handle)?->name() ?? $handle,
            ])
            ->all();
    }

    private function missingSites(Entry $entry)
    {
        return $entry->collection()->sites()
            ->reject($entry->locale())
            ->reject(fn ($site) => $entry->existsIn($site))
            ->values();
    }
}
