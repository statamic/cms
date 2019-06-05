<?php

namespace Statamic\Actions;

use Statamic\API;

class Delete extends Action
{
    protected $dangerous = true;

    public function visibleTo($key, $context)
    {
        if ($key === 'entries') {
            return false;
        }

        if ($key === 'users') {
            return user()->can('delete users');
        }

        return true;
    }

    public function run($items)
    {
        $items->each->delete();
    }
}
