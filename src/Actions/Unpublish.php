<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Unpublish extends Action
{
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
        return [
            'single' => 'Are you sure you want to unpublish this entry?',
            'plural' => 'Are you sure you want to unpublish these :count entries?'
        ];
    }

    public function buttonText()
    {
        return [
            'single' => 'Unpublish Entry',
            'plural' => 'Unpublish :count Entries'
        ];
    }

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->unpublish(['user' => User::current()]);
        });
    }
}
