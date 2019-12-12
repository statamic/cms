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

    public function buttonText()
    {
        /** @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items)
    {
        $items->each->delete();
    }
}
