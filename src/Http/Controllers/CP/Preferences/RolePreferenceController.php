<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Statamic\Facades\Role;
use Statamic\Http\Controllers\CP\CpController;

class RolePreferenceController extends CpController
{
    use ManagesPreferences;

    private $currentHandle;

    public function edit($role)
    {
        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        $this->currentHandle = $role->handle();

        return $this->view(
            $role->title(),
            cp_route('preferences.role.update', $role->handle()),
            $role->preferences(),
        );
    }

    public function update(Request $request, $role)
    {
        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        return $this->updatePreferences($request, $role);
    }

    private function ignoreSaveAsOption()
    {
        return $this->currentHandle;
    }
}
