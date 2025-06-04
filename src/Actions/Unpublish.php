<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Unpublish extends Action
{
    protected $icon = 'eye-closed';

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list' && $item instanceof Entry && $item->published();
    }

    public function visibleToBulk($items)
    {
        if ($items->whereInstanceOf(Entry::class)->count() !== $items->count()) {
            return false;
        }

        if ($items->reject->published()->count() === $items->count()) {
            return false;
        }

        return true;
    }

    public function authorize($user, $entry)
    {
        return $user->can('publish', $entry);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to unpublish this entry?|Are you sure you want to unpublish these :count entries?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Unpublish Entry|Unpublish :count Entries';
    }

    public function run($entries, $values)
    {
        $failures = $entries->reject(fn ($entry) => $entry->unpublish(['user' => User::current()]));
        $total = $entries->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();
            if ($total === 1) {
                throw new \Exception(__('Entry could not be unpublished'));
            } elseif ($success === 0) {
                throw new \Exception(__('Entries could not be unpublished'));
            } else {
                throw new \Exception(__(':success/:total entries were unpublished', ['total' => $total, 'success' => $success]));
            }
        }

        return trans_choice('Entry unpublished|Entries unpublished', $total);
    }
}
