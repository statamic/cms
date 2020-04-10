<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Unpublish extends Action
{
    public function filter($item)
    {
        return $item instanceof Entry && $item->published();
    }

    public function filterBulk($items)
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

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->unpublish(['user' => User::current()]);
        });
    }
}
