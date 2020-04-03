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

    public function filter($item)
    {
        return $item instanceof Entry;
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

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->publish(['user' => User::current()]);
        });
    }
}
