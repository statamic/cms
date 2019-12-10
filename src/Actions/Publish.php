<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Publish extends Action
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
            'single' => 'Are you sure you want to publish this entry?',
            'plural' => 'Are you sure you want to publish these :count entries?'
        ];
    }

    public function buttonText()
    {
        return [
            'single' => 'Publish Entry',
            'plural' => 'Publish :count Entries'
        ];
    }

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->publish(['user' => User::current()]);
        });
    }
}
