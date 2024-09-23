<?php

namespace Statamic\Actions;

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
        $failures = $entries->reject(fn ($entry) => $entry->publish(['user' => User::current()]));
        $total = $entries->count();

        if ($failures->isNotEmpty()) {
            $success = $total - $failures->count();
            if ($total === 1) {
                throw new \Exception(__('Entry could not be published'));
            } elseif ($success === 0) {
                throw new \Exception(__('Entries could not be published'));
            } else {
                throw new \Exception(__(':success/:total entries were published', ['total' => $total, 'success' => $success]));
            }
        }

        return trans_choice('Entry published|Entries published', $total);
    }
}
