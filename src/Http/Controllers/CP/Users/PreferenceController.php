<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
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
            'value' => 'sometimes',
        ]);

        $method = $request->has('append') ? 'appendPreference' : 'setPreference';

        User::current()
            ->{$method}($request->key, $request->value)
            ->cleanupPreference($request->key)
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

        User::current()
            ->removePreference($key, $request->value, $request->input('cleanup', true))
            ->save();

        return Preference::all();
    }
}
