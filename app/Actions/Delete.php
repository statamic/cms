<?php

namespace Statamic\Actions;

use Statamic\Facades;

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

    public function authorize($item)
    {
        return user()->can('delete', $item);
    }

    public function run($items)
    {
        $items->each->delete();
    }
}
