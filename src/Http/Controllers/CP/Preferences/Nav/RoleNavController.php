<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Nav\Nav as NavResource;

class RoleNavController extends NavController
{
    public function edit($handle = null)
    {
        abort_unless(User::current()->isSuper(), 403);

        abort_unless($role = Role::find($handle), 404);

        return $this->navBuilder([
            'title' => $role->title().' Nav',
            'updateUrl' => cp_route('preferences.nav.role.update', $role->handle()),
            'currentNav' => NavResource::make(Nav::build()),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(User::current()->isSuper(), 403);

        abort_unless($role = Role::find($handle), 404);

        $nav = $this->getUpdatedNav($request);

        $role->setPreference('nav', $nav)->save();

        return true;
    }
}
