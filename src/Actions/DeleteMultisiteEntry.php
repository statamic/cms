<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Statamic;

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
                'display' => __('Localizations'),
                'instructions' => __('statamic::messages.choose_entry_localization_deletion_behavior').' <a href="'.Statamic::docsUrl('/tips/localizing-entries#deleting').'" target="_blank">'.__('Learn more').'</a>',
                'type' => 'button_group',
                'options' => [
                    'delete' => __('Delete'),
                    'copy' => __('Detach'),
                ],
                'validate' => 'required',
            ],
        ];
    }

    public function buttonText()
    {
        /* @translation */
        return 'Confirm';
    }

    public function run($items, $values)
    {
        $behavior = $values['behavior'];

        if ($behavior === 'copy') {
            $items->each->detachLocalizations();
        } else {
            $items->each->deleteDescendants();
        }

        $items->each->delete();
    }
}
