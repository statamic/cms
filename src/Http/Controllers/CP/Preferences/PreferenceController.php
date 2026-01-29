<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;

class PreferenceController extends CpController
{
    public function index()
    {
        if (! Statamic::pro() || User::current()->cannot('manage preferences')) {
            return redirect()->route('statamic.cp.preferences.user.edit');
        }

        return Inertia::render('preferences/Index', [
            'userPreferences' => User::current()->preferences(),
            'userPreferencesUrl' => cp_route('preferences.user.edit'),
            'defaultPreferences' => Preference::default()->all(),
            'defaultPreferencesUrl' => cp_route('preferences.default.edit'),
            'roles' => Role::all()->map(fn ($role) => [
                'handle' => $role->handle(),
                'title' => __($role->title()),
                'preferences' => $role->preferences(),
                'editUrl' => cp_route('preferences.role.edit', [$role->handle()]),
            ])->values(),
        ]);
    }

    /**
     * Store a user preference.
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
     * @param  string  $key
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
