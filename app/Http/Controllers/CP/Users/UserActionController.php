<?php

namespace Statamic\Http\Controllers\CP\Users;

use Statamic\API\User;
use Statamic\Http\Controllers\CP\ActionController;

class UserActionController extends ActionController
{
    protected static $key = 'users';

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return User::find($item);
        });
    }
}
