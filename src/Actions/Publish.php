<?php

namespace Statamic\Actions;

use Exception;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Publish extends Action
{
    public static function title()
    {
        return __('Publish');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list' && $item instanceof Entry && ! $item->published();
    }

    public function visibleToBulk($items)
    {
        if ($items->whereInstanceOf(Entry::class)->count() !== $items->count()) {
            return false;
        }

        if ($items->filter->published()->count() === $items->count()) {
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
        return 'Are you sure you want to publish this entry?|Are you sure you want to publish these :count entries?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Publish Entry|Publish :count Entries';
    }

    public function run($entries, $values)
    {
        $failedActions = $entries->filter(function ($entry) {
            return ! $entry->publish(['user' => User::current()]);
        });

        if ($failedActions->isNotEmpty()) {
            /** @translation */
            throw new Exception(__("Couldn't publish entry"));
        }
    }
}
