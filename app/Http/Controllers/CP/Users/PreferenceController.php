<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\API\Preference;
use Statamic\Http\Controllers\CP\CpController;

class PreferenceController extends CpController
{
    /**
     * Store a user preference.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->authorize('access cp');

        $request->validate([
            'key' => 'required',
            'value' => 'required',
        ]);

        $method = $request->has('append') ? 'appendPreference' : 'setPreference';

        auth()->user()
            ->{$method}($request->key, $request->value)
            ->save();

        return Preference::all();
    }

    /**
     * Destroy a user preference.
     *
     * @param string $key
     */
    public function destroy($key, Request $request)
    {
        $this->authorize('access cp');

        auth()->user()
            ->removePreference($key, $request->value, $request->input('cleanup', true))
            ->save();

        return Preference::all();
    }
}
