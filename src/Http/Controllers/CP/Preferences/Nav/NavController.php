<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Inertia\Inertia;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Statamic;

class NavController extends Controller
{
    public function index()
    {
        if (! Statamic::pro() || User::current()->cannot('manage preferences')) {
            return redirect(cp_route('preferences.nav.user.edit'));
        }

        return Inertia::render('preferences/nav/Index', [
            'userPreferences' => User::current()->hasPreference('nav') ? ['nav' => true] : [],
            'userPreferencesUrl' => cp_route('preferences.nav.user.edit'),
            'defaultPreferences' => Preference::default()->hasPreference('nav') ? ['nav' => true] : [],
            'defaultPreferencesUrl' => cp_route('preferences.nav.default.edit'),
            'roles' => Role::all()->map(fn ($role) => [
                'handle' => $role->handle(),
                'title' => __($role->title()),
                'preferences' => $role->hasPreference('nav') ? ['nav' => true] : [],
                'editUrl' => cp_route('preferences.nav.role.edit', [$role->handle()]),
            ])->values(),
        ]);
    }
}
