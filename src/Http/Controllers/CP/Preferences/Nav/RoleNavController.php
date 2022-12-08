<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;

class RoleNavController extends Controller
{
    use Concerns\HasNavBuilder;

    public function edit($handle)
    {
        abort_unless(User::current()->isSuper(), 403);

        abort_unless($role = Role::find($handle), 404);

        $preferences = $role->getPreference('nav') ?? Preference::default()->get('nav');

        $nav = $preferences
            ? Nav::build($preferences)
            : Nav::buildWithoutPreferences();

        return $this->navBuilder($nav, [
            'title' => $role->title().' Nav',
            'updateUrl' => cp_route('preferences.nav.role.update', $role->handle()),
            'destroyUrl' => cp_route('preferences.nav.role.destroy', $role->handle()),
        ]);
    }

    public function update(Request $request, $handle)
    {
        abort_unless(User::current()->isSuper(), 403);

        abort_unless($role = Role::find($handle), 404);

        $nav = $this->getUpdatedNav($request);

        $role->setPreference('nav', $nav)->save();

        return true;
    }

    public function destroy($handle)
    {
        abort_unless(User::current()->isSuper(), 403);

        abort_unless($role = Role::find($handle), 404);

        $role->removePreference('nav')->save();

        return true;
    }
}
