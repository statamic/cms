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

    public function authorize($entry)
    {
        return User::current()->can('publish', $entry);
    }

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->publish(['user' => User::current()]);
        });
    }
}
