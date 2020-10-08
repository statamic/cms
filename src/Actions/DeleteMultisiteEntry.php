<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;

class DeleteMultisiteEntry extends Delete
{
    public function visibleTo($item)
    {
        return $item instanceof Entry
            && $item->collection()->sites()->count() > 1;
    }

    public function fieldItems()
    {
        return [
            'behavior' => [
                'display' => 'Localization handling',
                'instructions' => 'How would you like to handle localized versions of the entries being deleted? <a href="" target="_blank">More info.</a>',
                'type' => 'radio',
                'options' => [
                    'delete' => 'Delete localized versions',
                    'copy' => 'Copy data to localizations',
                ],
                'validate' => 'required',
            ],
        ];
    }

    public function run($items, $values)
    {
        $behavior = $values['behavior'];

        if ($behavior === 'copy') {
            $this->copyDataToLocalizations($items);
        } else {
            $this->deleteDescendants($items);
        }

        $items->each->delete();
    }

    private function deleteDescendants($entries)
    {
        $entries->each(function ($entry) {
            $entry->descendants()->each->delete();
        });
    }

    private function copyDataToLocalizations($entries)
    {
        $entries->each(function ($origin) {
            Facades\Entry::query()
                ->where('collection', $origin->collectionHandle())
                ->where('origin', $origin->id())
                ->get()
                ->each(function ($loc) use ($origin) {
                    $loc
                        ->origin(null)
                        ->data($origin->data()->merge($loc->data()))
                        ->save();
                });
        });
    }
}
