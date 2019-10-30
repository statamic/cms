<?php

namespace Statamic\Actions;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\User;

class Delete extends Action
{
    protected $dangerous = true;

    public function filter($item)
    {
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
