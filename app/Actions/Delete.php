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

        return true;
    }

    public function run($items)
    {
        $items->each->delete();
    }
}
