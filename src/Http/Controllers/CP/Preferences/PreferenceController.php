<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class PreferenceController extends CpController
{
    public function index()
    {
        return redirect()->route('statamic.cp.preferences.edit');
    }

    public function edit()
    {
        $blueprint = $this->blueprint();

        $fields = $blueprint->fields()->addValues(Preference::all())->preProcess();

        return view('statamic::preferences.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function update(Request $request)
    {
        $fields = $this->blueprint()->fields()->addValues($request->all())->process();

        User::current()->mergePreferences($fields->values()->all())->save();
    }

    private function blueprint()
    {
        return Blueprint::makeFromSections(Preference::sections());
    }

    /**
     * Store a user preference.
     *
     * @param  Request  $request
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
