<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Statamic\Facades\Preference;
use Statamic\Http\Controllers\CP\CpController;

class DefaultPreferenceController extends CpController
{
    use ManagesPreferences;

    public function edit()
    {
        return $this->view(
            __('Default'),
            cp_route('preferences.default.update'),
            Preference::default()->all()
        );
    }

    public function update(Request $request)
    {
        return $this->updatePreferences($request, Preference::default());
    }

    private function ignoreSaveAsOption()
    {
        return 'default';
    }
}
