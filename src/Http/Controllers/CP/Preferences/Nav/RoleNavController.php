<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Http\Controllers\Controller;

class RoleNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected $currentHandle;

    protected function ignoreSaveAsOption()
    {
        return $this->currentHandle;
    }

    public function edit($handle)
    {
        abort_unless($role = Role::find($handle), 404);

        $this->currentHandle = $handle;

        $preferences = $role->getPreference('nav') ?? Preference::default()->get('nav');

        $nav = $preferences
            ? Nav::build($preferences, true)
            : Nav::buildWithoutPreferences(true);

        return $this->navBuilder($nav, [
            'title' => $role->title(),
            'updateUrl' => cp_route('preferences.nav.role.update', $role->handle()),
            'destroyUrl' => cp_route('preferences.nav.role.destroy', $role->handle()),
        ]);
    }

    public function update(Request $request, $handle)
    {
        abort_unless($role = Role::find($handle), 404);

        $nav = $this->getUpdatedNav($request);

        $role->setPreference('nav', $nav)->save();

        $this->success(__('Saved'));

        return true;
    }

    public function destroy($handle)
    {
        abort_unless($role = Role::find($handle), 404);

        $role->removePreference('nav')->save();

        return true;
    }
}
