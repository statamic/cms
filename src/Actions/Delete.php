<?php

namespace Statamic\Actions;

class Delete extends Action
{
    protected $dangerous = true;

    public function filter($item)
    {
        return true;
    }

    public function authorize($user, $item)
    {
        return $user->can('delete', $item);
    }

    public function run($items)
    {
        $items->each->delete();
    }
}
