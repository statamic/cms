<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Site;
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
        if (! $this->canChangeBehaviour()) {
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
        $behavior = $this->canChangeBehaviour() ? $values['behavior'] : 'copy';

        if ($behavior === 'copy') {
            $items->each->detachLocalizations();
        } else {
            $items->each->deleteDescendants();
        }

        $items->each->delete();
    }

    private function canChangeBehaviour(): bool
    {
        if (! Site::multiEnabled()) {
            return true;
        }

        return $this->items->every(function ($entry) {
            return $entry->isRoot()
                ? $entry->descendants()->every(fn ($descendant) => User::current()->can("access {$descendant->site()->handle()} site"))
                : $entry->ancestors()->every(fn ($ancestor) => User::current()->can("access {$ancestor->site()->handle()} site"));
        });
    }
}
