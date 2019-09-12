<?php

namespace Statamic\Actions;

use Statamic\Facades;
use Statamic\Facades\User;
use Statamic\Facades\Collection;
use Statamic\Contracts\Entries\Entry;

class Unpublish extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'entries';
    }

    public function authorize($entry)
    {
        return user()->can('publish', [Entry::class, $entry->collection()]);
    }

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->unpublish(['user' => User::current()]);
        });
    }
}
