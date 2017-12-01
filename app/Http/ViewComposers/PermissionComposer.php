<?php

namespace Statamic\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Statamic\API\Folder;
use Statamic\API\User;

class PermissionComposer
{
    public function compose(View $view)
    {
        $permissions = User::getCurrent()->permissions();

        $view->with('permissions', base64_encode(json_encode($permissions)));
    }
}