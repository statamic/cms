<?php

namespace Statamic\Actions;

use Statamic\Facades\User;

class Publish extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'entries';
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
