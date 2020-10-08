<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;

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
            throw new \Exception('copying not implemented yet');
        }

        $items->each(function ($item) {
            $item->descendants()->each->delete();
            $item->delete();
        });
    }
}
