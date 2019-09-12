<?php

namespace Statamic\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Statamic\Facades\Folder;
use Statamic\Facades\User;

class PermissionComposer
{
    public function compose(View $view)
    {
        $permissions = User::current()->permissions();

        \Statamic::provideToScript([
            'permissions' => base64_encode(json_encode($permissions))
        ]);
    }
}
