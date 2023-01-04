<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class UserPreferenceController extends CpController
{
    use ManagesPreferences;

    public function edit()
    {
        return $this->view(
            __('My Preferences'),
            cp_route('preferences.user.update'),
            User::current()->preferences()
        );
    }

    public function update(Request $request)
    {
        return $this->updatePreferences($request, User::current());
    }

    private function ignoreSaveAsOption()
    {
        return 'user';
    }
}
