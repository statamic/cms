<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;
use Statamic\Statamic;

class DeleteMultisiteEntry extends Delete
{
    public function visibleTo($item)
    {
        if (! ($item instanceof Entry && $item->collection()->sites()->count() > 1)) {
            return false;
        }

        return ! $item->page()?->isRoot();
    }

    public function fieldItems()
    {
        if (! $this->canChangeBehavior()) {
            return [];
        }

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
        $behavior = $this->canChangeBehavior() ? $values['behavior'] : 'copy';

        if ($behavior === 'copy') {
            $items->each->detachLocalizations();
        } else {
            $items->each->deleteDescendants();
        }

        $failures = $items->reject(fn ($entry) => $entry->delete());
        $total = $items->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();
            if ($total === 1) {
                throw new \Exception(__('Entry could not be deleted'));
            } elseif ($success === 0) {
                throw new \Exception(__('Entries could not be deleted'));
            } else {
                throw new \Exception(__(':success/:total entries were deleted', ['total' => $total, 'success' => $success]));
            }
        }

        return trans_choice('Entry deleted|Entries deleted', $total);
    }

    private function canChangeBehavior(): bool
    {
        return $this->items->every(function ($entry) {
            $descendants = $entry->descendants();

            return $descendants->isNotEmpty()
                && $descendants->every(fn ($descendant) => User::current()->can('view', $descendant->site()));
        });
    }
}
