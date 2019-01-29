<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\User;

class UserActionController extends EntryActionController
{
    protected function getSelectedItems($items)
    {
        return $items->map(function ($item) {
            return User::find($item);
        });
    }
}
