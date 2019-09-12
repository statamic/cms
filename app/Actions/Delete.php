<?php

namespace Statamic\Actions;

use Statamic\Facades\User;

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
        return User::current()->can('delete', $item);
    }

    public function run($items)
    {
        $items->each->delete();
    }
}
